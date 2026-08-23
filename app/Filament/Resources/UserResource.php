<?php

namespace App\Filament\Resources;

use App\Filament\Pages\UserTaskHistory;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = null;

    protected static ?string $navigationLabel = 'Employees';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('WhatsApp Number')
                            ->tel()
                            ->helperText('Include country code, for example: 923001234567.')
                            ->maxLength(25),

                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255),

                        Forms\Components\Select::make('project_id')
                            ->label('Project')
                            ->options(Project::query()->orderByRaw("name = 'BPOVO' DESC")->orderBy('name')->pluck('name', 'id'))
                            ->default(fn () => Project::where('name', 'BPOVO')->value('id'))
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('department_id', null);
                                $set('job_responsibility', null);
                            })
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('department_id')
                            ->label('Department')
                            ->options(function (callable $get) {
                                $projectId = $get('project_id');

                                return $projectId
                                    ? Department::where('project_id', $projectId)->orderBy('name')->pluck('name', 'id')
                                    : [];
                            })
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('job_responsibility', null))
                            ->required(),

                        Forms\Components\Select::make('job_responsibility')
                            ->label('Role & Responsibility')
                            ->options(function (callable $get) {
                                $departmentId = $get('department_id');
                                if (! $departmentId) {
                                    return [];
                                }

                                $department = Department::find($departmentId);
                                if (! $department || empty($department->responsibilities)) {
                                    return [];
                                }

                                return array_combine($department->responsibilities, $department->responsibilities);
                            })
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            ->dehydrated(false)
                            ->saveRelationshipsUsing(function (User $record, $state) {
                                $roleIds = is_array($state) ? $state : [$state];
                                $record->roles()->sync($roleIds);

                                if (! empty($roleIds)) {
                                    $role = Role::find($roleIds[0]);
                                    if ($role) {
                                        $record->updateQuietly(['role' => $role->name]);
                                    }
                                }
                            })
                            ->label('Account Type')
                            ->default(fn () => Role::where('name', 'employee')->value('id'))
                            ->preload()
                            ->searchable()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Direct Custom Permissions (Optional)')
                    ->description('Assign specific direct permissions to this user individually if needed')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->relationship('permissions', 'name')
                            ->options(function () {
                                /** @var User $authUser */
                                $authUser = Auth::user();

                                if ($authUser->hasRole('admin')) {
                                    return Permission::all()->pluck('name', 'id');
                                }

                                return $authUser->getAllPermissions()->pluck('name', 'id');
                            })
                            ->columns(3)
                            ->gridDirection('row')
                            ->bulkToggleable()
                            ->searchable(),
                    ])
                    ->hiddenOn('create')
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('WhatsApp')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'admin' => 'danger',
                        'manager' => 'warning',
                        'employee' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable(),

                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->sortable(),

                Tables\Columns\TextColumn::make('performance')
                    ->label('Performance')
                    ->state(function (User $record): string {
                        return number_format($record->getPerformancePercentage(), 0).'%';
                    })
                    ->badge()
                    ->color(function (User $record): string {
                        $perf = $record->getPerformancePercentage();
                        if ($perf >= 90) {
                            return 'success';
                        }
                        if ($perf >= 70) {
                            return 'warning';
                        }

                        return 'danger';
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->options(Project::query()->orderByRaw("name = 'BPOVO' DESC")->orderBy('name')->pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->label('Filter by Role'),
            ])
            ->actions([
                Tables\Actions\Action::make('history')
                    ->label('History')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->visible(fn (): bool => UserTaskHistory::canAccess())
                    ->url(fn (User $record): string => UserTaskHistory::getUrl().'?user='.$record->id),
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
            RelationManagers\TasksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
