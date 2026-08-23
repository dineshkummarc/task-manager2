<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = null;
    protected static ?string $navigationLabel = 'Roles & Permissions';
    protected static ?int $navigationSort = 4;

    // Only admin can see this in the sidebar
    public static function canViewAny(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        // isAdmin() checks both Spatie role AND the 'role' column in users table
        return $user?->isAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Role Information')
                    ->description('Manage role details and assigned system permissions')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Role Name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->disabled(fn (?Role $record): bool => strtolower((string) $record?->name) === 'admin')
                            ->helperText(fn (?Role $record): ?string => strtolower((string) $record?->name) === 'admin'
                                ? 'The Admin role name is locked.'
                                : null),

                        Forms\Components\CheckboxList::make('permissions')
                            ->relationship('permissions', 'name')
                            ->label('Assigned Permissions')
                            ->options(function () {
                                // Admin can assign any permission;
                                // anyone else (shouldn't reach here but just in case)
                                // only sees their own permissions
                                if (Auth::user()?->isAdmin()) {
                                    return Permission::all()->pluck('name', 'id');
                                }
                                return Auth::user()?->getAllPermissions()->pluck('name', 'id') ?? [];
                            })
                            ->columns(3)
                            ->gridDirection('row')
                            ->bulkToggleable()
                            ->helperText('Check the boxes to grant specific permissions to this role.'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Role')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'admin' => 'danger',
                        'manager' => 'warning',
                        'employee' => 'success',
                        default => 'primary',
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions Count')
                    ->badge()
                    ->color('info')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('permissions.name')
                    ->label('Permissions')
                    ->badge()
                    ->separator(',')
                    ->limitList(4)
                    ->expandableLimitedList()
                    ->visibleFrom('md'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit Permissions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
