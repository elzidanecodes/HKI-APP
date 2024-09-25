<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlatBeratsResource\Pages;
use App\Filament\Resources\AlatBeratsResource\RelationManagers;
use App\Models\AlatBerats;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                FileUpload::make('foto_sio')
                    ->label('Foto SIO')
                    ->image()
                    ->required(),
                FileUpload::make('foto_silo') 
                    ->label('Foto SILO')
                    ->image()
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_alat')
                    ->label('ID Alat')
                    ->sortable(),
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
                TextColumn::make('foto_sio')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('foto_silo')
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
            'index' => Pages\ListAlatBerats::route('/'),
            'create' => Pages\CreateAlatBerats::route('/create'),
            'edit' => Pages\EditAlatBerats::route('/{record}/edit'),
        ];
    }    
}
