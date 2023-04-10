<?php

namespace App\Filament\Resources\GithubTrendingMonthlyResource\Pages;

use App\Filament\Resources\GithubTrendingMonthlyResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGithubTrendingMonthlies extends ListRecords
{
    protected static string $resource = GithubTrendingMonthlyResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
