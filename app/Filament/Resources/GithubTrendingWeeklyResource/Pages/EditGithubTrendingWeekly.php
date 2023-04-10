<?php

namespace App\Filament\Resources\GithubTrendingWeeklyResource\Pages;

use App\Filament\Resources\GithubTrendingWeeklyResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGithubTrendingWeekly extends EditRecord
{
    protected static string $resource = GithubTrendingWeeklyResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
