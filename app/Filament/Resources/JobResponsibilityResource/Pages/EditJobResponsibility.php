<?php

namespace App\Filament\Resources\JobResponsibilityResource\Pages;

use App\Filament\Resources\JobResponsibilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJobResponsibility extends EditRecord
{
    protected static string $resource = JobResponsibilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
