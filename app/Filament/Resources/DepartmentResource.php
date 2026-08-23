<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Department;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Select::make('project_id')
                ->label('Project')
                ->options(Project::query()->orderByRaw("name = 'BPOVO' DESC")->orderBy('name')->pluck('name', 'id'))
                ->default(fn () => Project::where('name', 'BPOVO')->value('id'))
                ->required()
                ->searchable()
                ->preload(),

            Forms\Components\TextInput::make('name')
                ->label('Department Name')
                ->required()
                ->maxLength(255),

            Forms\Components\TagsInput::make('responsibilities')
                ->label('Job Responsibilities')
                ->placeholder('Type and press enter (e.g. Social Media, Email Marketing)')
                ->helperText('Type a responsibility and press Enter to add another.'),
        ])
        ->columns(2); // Yeh laptop screen par dono fields ko aik hi row (side-by-side) mein kar dega, aur mobile par automatically upar-neeche adjust ho jayega.
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->options(Project::query()->orderByRaw("name = 'BPOVO' DESC")->orderBy('name')->pluck('name', 'id')),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
