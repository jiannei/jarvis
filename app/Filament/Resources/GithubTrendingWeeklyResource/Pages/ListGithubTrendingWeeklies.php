<?php

namespace App\Filament\Resources\GithubTrendingWeeklyResource\Pages;

use App\Filament\Resources\GithubTrendingWeeklyResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGithubTrendingWeeklies extends ListRecords
{
    protected static string $resource = GithubTrendingWeeklyResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
