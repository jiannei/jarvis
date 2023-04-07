<?php

namespace App\Filament\Resources\GithubTrendingDailyResource\Pages;

use App\Filament\Resources\GithubTrendingDailyResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGithubTrendingDaily extends CreateRecord
{
    protected static string $resource = GithubTrendingDailyResource::class;
}
