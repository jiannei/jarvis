<?php

namespace App\Filament\Resources\CrawlTaskResource\Pages;

use App\Filament\Resources\CrawlTaskResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCrawlTask extends EditRecord
{
    protected static string $resource = CrawlTaskResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['pattern'] = json_encode($data['pattern'],JSON_PRETTY_PRINT);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['pattern'] = json_decode($data['pattern'],true);

        return $data;
    }
}
