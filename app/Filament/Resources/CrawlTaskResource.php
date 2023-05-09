<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CrawlTaskResource\Pages;
use App\Models\CrawlTask;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Collection;

class CrawlTaskResource extends Resource
{
    protected static ?string $model = CrawlTask::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('expression')
                    ->required(),
                Forms\Components\Textarea::make('pattern')
                    ->required(),
                Forms\Components\Toggle::make('active')->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('expression'),
                Tables\Columns\IconColumn::make('active')->boolean(),
                Tables\Columns\BadgeColumn::make('status')->getStateUsing(function (CrawlTask $record) {
                    return match ($record->status) {
                        default => '初始',
                        -1 => '失败',
                        1 => '成功',
                    };
                })->colors([
                    'primary',
                    'success' => static fn ($state): bool => $state === '成功',
                    'danger' => static fn ($state): bool => $state === '失败',
                ]),
                Tables\Columns\TextColumn::make('previous_run_date')->sortable()
                    ->dateTime(),
                Tables\Columns\TextColumn::make('next_run_date')->sortable()
                    ->dateTime(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('toggle')
                    ->action(fn (Collection $records) => $records->each->toggle())
                    ->requiresConfirmation()
                    ->color('success')
                    ->icon('heroicon-o-check'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCrawlTasks::route('/'),
            'create' => Pages\CreateCrawlTask::route('/create'),
            'edit' => Pages\EditCrawlTask::route('/{record}/edit'),
        ];
    }
}
