<?php

namespace App\Filament\Resources\CrawlTaskResource\Pages;

use App\Filament\Resources\CrawlTaskResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCrawlTask extends CreateRecord
{
    protected static string $resource = CrawlTaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
       $data['pattern'] = json_decode($data['pattern'],true);

        return $data;
    }

}
