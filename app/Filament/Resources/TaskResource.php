<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\SelectFilter;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                
                Forms\Components\Textarea::make('description')
                    ->rows(5)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('estimated_minutes')
                    ->label('Estimated Time (Minutes)')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->helperText('Enter the estimated duration in minutes.'),

                // 1. Project Select
                Forms\Components\Select::make('project_id')
                    ->label('Project')
                    ->options(Project::query()->orderByRaw("name = 'BPOVO' DESC")->orderBy('name')->pluck('name', 'id'))
                    ->default(fn () => Project::where('name', 'BPOVO')->value('id'))
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        $set('department_id', null);
                        $set('job_responsibility', null);
                        $set('assigned_to', null);
                    })
                    ->searchable()
                    ->preload()
                    ->required(),

                // 2. Department Select
                Forms\Components\Select::make('department_id')
                    ->label('Department')
                    ->options(function (callable $get) {
                        $projectId = $get('project_id');

                        return $projectId
                            ? \App\Models\Department::where('project_id', $projectId)->orderBy('name')->pluck('name', 'id')
                            : [];
                    })
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        $set('job_responsibility', null);
                        $set('assigned_to', null);
                    })
                    ->searchable()
                    ->preload()
                    ->required(),

                // 3. Role & Responsibility Select
                Forms\Components\Select::make('job_responsibility')
                    ->label('Role & Responsibility')
                    ->options(function (callable $get) {
                        $departmentId = $get('department_id');
                        if (! $departmentId) {
                            return [];
                        }
                        
                        $department = \App\Models\Department::find($departmentId);
                        if (! $department || empty($department->responsibilities)) {
                            return [];
                        }

                        return array_combine($department->responsibilities, $department->responsibilities);
                    })
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('assigned_to', null))
                    ->searchable()
                    ->preload()
                    ->required(),

                // 4. Assign To (Employee Select)
                Forms\Components\Select::make('assigned_to')
                    ->label('Assign To (Employee)')
                    ->options(function (callable $get) {
                        $departmentId = $get('department_id');
                        $projectId = $get('project_id');
                        $responsibility = $get('job_responsibility');

                        if (! $projectId || ! $departmentId || ! $responsibility) {
                            return [];
                        }

                        return \App\Models\User::where('project_id', $projectId)
                            ->where('department_id', $departmentId)
                            ->where('job_responsibility', $responsibility)
                            ->where('role', 'employee')
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                    ])
                    ->default('pending')
                    ->required(),
            ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->action(
                        Tables\Actions\Action::make('view_description')
                            ->form([
                                Forms\Components\Textarea::make('description')
                                    ->disabled()
                                    ->default(fn ($record) => $record->description)
                                    ->columnSpanFull()
                                    ->rows(5),
                                Forms\Components\Textarea::make('block_reason')
                                    ->label('Block Reason')
                                    ->disabled()
                                    ->default(fn ($record) => $record->block_reason)
                                    ->columnSpanFull()
                                    ->visible(fn ($record) => filled($record->block_reason))
                            ])
                            ->modalHeading(fn ($record) => $record->title)
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Close')
                    ),
                Tables\Columns\TextColumn::make('assignee.name')->label('Assigned To'),
                Tables\Columns\TextColumn::make('creator.name')->label('Assigned By'),
                Tables\Columns\TextColumn::make('project.name')->label('Project'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'pending',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                    ]),
                Tables\Columns\TextColumn::make('estimated_minutes')
                    ->label('Est. Time (Mins)')
                    ->numeric(),
                Tables\Columns\IconColumn::make('is_blocked')
                    ->boolean()
                    ->label('Blocked'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Started At')
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('project_id')
                    ->label('Project')
                    ->options(Project::query()->orderByRaw("name = 'BPOVO' DESC")->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(\App\Models\Department::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        $user = Auth::user();
        $userObj = is_object($user) ? $user : null;
        if ($userObj && method_exists($userObj, 'hasRole') && $userObj->hasRole('employee')) {
            $query->where('assigned_to', $userObj->id);
        }
        
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
