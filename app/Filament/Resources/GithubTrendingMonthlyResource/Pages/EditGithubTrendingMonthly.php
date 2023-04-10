<?php

namespace App\Filament\Resources\GithubTrendingMonthlyResource\Pages;

use App\Filament\Resources\GithubTrendingMonthlyResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGithubTrendingMonthly extends EditRecord
{
    protected static string $resource = GithubTrendingMonthlyResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
