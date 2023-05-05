<?php

namespace App\Filament\Resources\CrawlTaskResource\Pages;

use App\Filament\Resources\CrawlTaskResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCrawlTasks extends ListRecords
{
    protected static string $resource = CrawlTaskResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
