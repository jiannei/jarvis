<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Models\Schedule;
use Cron\CronExpression;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Carbon;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('command'),
                Tables\Columns\TextColumn::make('parameters'),
                Tables\Columns\TextColumn::make('expression'),
                Tables\Columns\TextColumn::make('next')->getStateUsing(function (Schedule $record) {// todo 使用 pref 关联关系
                    return self::nextDueDate($record);
                }),
                Tables\Columns\IconColumn::make('active')->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')->sortable()
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSchedules::route('/'),
            //            'create' => Pages\CreateSchedule::route('/create'),
            //            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }

    private static function nextDueDate($schedule)
    {
        return Carbon::instance((new CronExpression($schedule->expression))
            ->getNextRunDate(Carbon::now()->setTimezone($schedule->timezone))
            ->setTimezone(new \DateTimeZone($schedule->timezone))
        )->diffForHumans();
    }
}
