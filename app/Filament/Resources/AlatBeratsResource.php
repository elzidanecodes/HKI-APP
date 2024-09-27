<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlatBeratsResource\Pages;
use App\Filament\Resources\AlatBeratsResource\RelationManagers;
use App\Models\AlatBerats;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class AlatBeratsResource extends Resource
{
    protected static ?string $model = AlatBerats::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_alat')
                    ->label('Nama Alat')
                    ->placeholder('Nama Alat')
                    ->required(),
                TextInput::make('merk_alat')
                    ->label('Merk Alat')
                    ->placeholder('Merk Alat')
                    ->required(),
                TextInput::make('tipe_alat')
                    ->label('Tipe Alat')
                    ->placeholder('Tipe Alat')
                    ->required(),
                DatePicker::make('tahun_produksi') 
                    -> label('Tahun Produksi')
                    ->displayFormat('Y') // Menampilkan hanya tahun
                    ->format('Y')        // Menyimpan hanya tahun ke dalam database
                    ->placeholder('Pilih tahun')
                    ->required(),
                SpatieMediaLibraryFileUpload::make('foto_sio')
                    ->label('Foto SIO')
                    ->collection('sio') // Nama koleksi untuk media
                    ->required(), // Atur ini jika diperlukan
                SpatieMediaLibraryFileUpload::make('foto_silo')
                    ->label('Foto SILO')
                    ->collection('silo') // Nama koleksi untuk media
                    ->required(), // Atur ini jika diperlukan
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
                TextColumn::make('nama_alat')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('merk_alat') 
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipe_alat') 
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tahun_produksi')
                    ->searchable()
                    ->sortable(),
                SpatieMediaLibraryImageColumn::make('foto_sio')
                ->label('Foto SIO')
                ->collection('sio'),
                SpatieMediaLibraryImageColumn::make('foto_silo')
                ->label('Foto SILO')
                ->collection('silo'),


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
            'index' => Pages\ListAlatBerats::route('/'),
            'create' => Pages\CreateAlatBerats::route('/create'),
            'edit' => Pages\EditAlatBerats::route('/{record}/edit'),
        ];
    }    
}
