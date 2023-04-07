<?php

namespace App\Filament\Resources\GithubTrendingDailyResource\Pages;

use App\Filament\Resources\GithubTrendingDailyResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGithubTrendingDailies extends ListRecords
{
    protected static string $resource = GithubTrendingDailyResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
