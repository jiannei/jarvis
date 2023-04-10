<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GithubTrendingMonthlyResource\Pages;
use App\Filament\Resources\GithubTrendingMonthlyResource\RelationManagers;
use App\Models\Github\TrendingMonthly;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GithubTrendingMonthlyResource extends Resource
{
    protected static ?string $model = TrendingMonthly::class;

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
                Tables\Columns\TextColumn::make('repo')->url(fn(TrendingMonthly $record):string => "https://github.com{$record->repo}")->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('desc')->toggleable()->tooltip(fn (Model $record): string => "{$record->desc}")->limit(50),
                Tables\Columns\TextColumn::make('language')->searchable(),
                Tables\Columns\TextColumn::make('stars')->sortable(),
                Tables\Columns\TextColumn::make('forks'),
                Tables\Columns\TextColumn::make('added_stars'),
                Tables\Columns\TextColumn::make('spoken_language_code'),
                Tables\Columns\TextColumn::make('month'),
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
            'index' => Pages\ListGithubTrendingMonthlies::route('/'),
//            'create' => Pages\CreateGithubTrendingMonthly::route('/create'),
//            'edit' => Pages\EditGithubTrendingMonthly::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
