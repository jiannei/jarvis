<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required(),
                Forms\Components\TextInput::make('link')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->required(),
                Forms\Components\TextInput::make('author')
                    ->required(),
                Forms\Components\TextInput::make('category'),
                Forms\Components\TextInput::make('publish_date')
                    ->required(),
                Forms\Components\TextInput::make('source')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('title')->limit(50)
                    ->tooltip(fn (Model $record): string => "{$record->title}")
                    ->url(fn (Post $record): string => $record->link)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('description')->limit(50)->tooltip(fn (Model $record): string => "{$record->description}")->visibleFrom('md'),
                Tables\Columns\TextColumn::make('author'),
                Tables\Columns\TextColumn::make('category'),
                Tables\Columns\TextColumn::make('publish_date'),
                Tables\Columns\TextColumn::make('source')->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')->sortable()
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('id', 'desc');
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
            'index' => Pages\ListPosts::route('/'),
            //            'create' => Pages\CreatePost::route('/create'),
            //            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
