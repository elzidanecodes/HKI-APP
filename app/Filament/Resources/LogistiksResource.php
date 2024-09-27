<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogistiksResource\Pages;
use App\Filament\Resources\LogistiksResource\RelationManagers;
use App\Models\Logistiks;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LogistiksResource extends Resource
{
    protected static ?string $model = Logistiks::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_barang')
                    ->label('Nama Barang')
                    ->placeholder('Nama Barang')
                    ->required(),
                TextInput::make('kategori_barang')
                    ->label('Kategori Barang')
                    ->placeholder('Kategori Barang')
                    ->required(),
                TextInput::make('deskripsi_barang')
                    ->label('Deskripsi Barang')
                    ->placeholder('Deskripsi Barang')
                    ->required(),
                TextInput::make('jumlah_barang')
                    ->label('Jumlah Barang')
                    ->placeholder('Jumlah Barang')
                    ->numeric() // Menandakan bahwa ini input angka
                    ->minValue(0) // Batas minimal
                    ->required(),
                TextInput::make('nama_vendor')
                    ->label('Nama Vendor')
                    ->placeholder('Nama Vendor')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('No')->getStateUsing(
                    static function ( $rowLoop, HasTable $livewire): string {
                        return (string) (
                            $rowLoop->iteration +
                            ($livewire->tableRecordsPerPage * (
                                $livewire->page - 1
                            ))
                        );
                    }
                ),
                TextColumn::make('nama_barang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kategori_barang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('deskripsi_barang') 
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jumlah_barang') 
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_vendor') 
                    ->searchable()
                    ->sortable(),
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
            'index' => Pages\ListLogistiks::route('/'),
            'create' => Pages\CreateLogistiks::route('/create'),
            'edit' => Pages\EditLogistiks::route('/{record}/edit'),
        ];
    }    
}
