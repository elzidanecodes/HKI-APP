<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogistiksResource\Pages;
use App\Models\Logistiks;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class LogistiksResource extends Resource
{
    protected static ?string $model = Logistiks::class;
    protected static ?string $navigationLabel = 'Logistik';
    protected static ?string $pluralModelLabel = 'Logistik';

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Data Material')
                    ->schema([
                        TextInput::make('nama_barang')
                            ->label('Nama Material')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('kategori_barang')
                            ->label('Kategori')
                            ->required()
                            ->maxLength(50),

                        Forms\Components\Textarea::make('deskripsi_barang')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('jumlah_barang')
                            ->label('Jumlah')
                            ->numeric()
                            ->required(),

                        Select::make('satuan')
                            ->label('Satuan')
                            ->options([
                                'zak' => 'Zak',
                                'batang' => 'Batang',
                                'meter' => 'Meter',
                                'unit' => 'Unit',
                            ])
                            ->required(),

                        TextInput::make('lokasi')
                            ->label('Lokasi')
                            ->placeholder('Gudang / Site'),

                        TextInput::make('nama_vendor')
                            ->label('Vendor')
                            ->maxLength(100),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_barang')
                    ->label('Material')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('kategori_barang')
                    ->label('Kategori')
                    ->colors([
                        'primary',
                    ]),

                BadgeColumn::make('jumlah_barang')
                    ->label('Stok')
                    ->formatStateUsing(fn ($record) =>
                        $record->jumlah_barang . ' ' . $record->satuan
                    )
                    ->colors([
                        'success',
                    ]),

                TextColumn::make('lokasi')
                    ->label('Lokasi')
                    ->toggleable()
                    ->placeholder('-'),

                TextColumn::make('nama_vendor')
                    ->label('Vendor')
                    ->toggleable()
                    ->placeholder('-'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Data Logistik')
                    ->modalWidth('lg'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->authorize('deleteAny'),
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
            'view' => Pages\ViewLogistiks::route('/{record}/view'),
        ];
    }    
}