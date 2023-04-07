<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GithubTrendingDailyResource\Pages;
use App\Filament\Resources\GithubTrendingDailyResource\RelationManagers;
use App\Models\Github\TrendingDaily;
use Carbon\CarbonInterface;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GithubTrendingDailyResource extends Resource
{
    protected static ?string $model = TrendingDaily::class;

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
                Tables\Columns\TextColumn::make('repo'),
                Tables\Columns\TextColumn::make('desc')->toggleable()->tooltip(fn (Model $record): string => "{$record->desc}")->limit(50),
                Tables\Columns\TextColumn::make('language')->searchable(),
                Tables\Columns\TextColumn::make('stars')->sortable(),
                Tables\Columns\TextColumn::make('forks'),
                Tables\Columns\TextColumn::make('added_stars'),
                Tables\Columns\TextColumn::make('spoken_language_code'),
                Tables\Columns\TextColumn::make('day'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
            ])
            ->bulkActions([
            ])
            ->defaultSort('id','desc');
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
            'index' => Pages\ListGithubTrendingDailies::route('/'),
//            'create' => Pages\CreateGithubTrendingDaily::route('/create'),
//            'edit' => Pages\EditGithubTrendingDaily::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
