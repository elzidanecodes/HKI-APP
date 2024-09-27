<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OperatorsResource\Pages;
use App\Models\Operators;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;

class OperatorsResource extends Resource
{
    protected static ?string $model = Operators::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_operator')
                    ->label('Nama Operator')
                    ->placeholder('Nama Operator')
                    ->required(),
                TextInput::make('nama_alat')
                    ->label('Nama Alat')
                    ->placeholder('Nama Alat')
                    ->required(),
                TextInput::make('nomor_silo')
                    ->label('Nomor Silo')
                    ->placeholder('Nomor Silo')
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
                    ->required(),
                TextInput::make('nomor_hp')
                    ->label('Nomor HP')
                    ->placeholder('Nomor HP')
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
                TextColumn::make('nama_operator')
                    ->searchable()
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
                TextColumn::make('nomor_hp') 
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
                Tables\Actions\ViewAction::make()
                    ->label('') // Label tombol
                    ->icon('heroicon-o-eye') // Ikon tombol,
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil')
                    ->color('primary'),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->successNotification(
                        Notification::make()
                        ->title('Data Operator Dihapus')
                        ->body('Data operator telah berhasil dihapus.')
                        ->success()
                        
                    ),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Hapus'),
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
            'index' => Pages\ListOperators::route('/'),
            'create' => Pages\CreateOperators::route('/create'),
            'edit' => Pages\EditOperators::route('/{record}/edit'),
            'view' => Pages\ViewOperators::route('/{record}/view'),
        ];
    }    
}
