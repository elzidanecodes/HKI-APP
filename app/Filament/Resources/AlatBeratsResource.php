<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlatBeratsResource\Pages;
use App\Filament\Resources\AlatBeratsResource\RelationManagers\OperatorAssignmentsRelationManager;
use App\Models\AlatBerats;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Resources\Table;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Support\Facades\Storage;

class AlatBeratsResource extends Resource
{
    protected static ?string $model = AlatBerats::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationLabel = 'Alat Berat';
    protected static ?string $pluralModelLabel = 'Alat Berat';

    /* =========================
     * FORM
     * ========================= */
    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\View::make('filament.components.silo-info')
            ->columnSpan('full'),


            // =========================
            // DATA ALAT BERAT (MASTER)
            // =========================
            Forms\Components\Section::make('Data Alat Berat')
                ->schema([
                    Forms\Components\TextInput::make('kode_alat')
                        ->label('Kode Alat')
                        ->required()
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('nama_alat')
                        ->label('Nama Alat')
                        ->required()
                        ->maxLength(35),

                    Forms\Components\TextInput::make('merk_alat')
                        ->label('Merk Alat')
                        ->required()
                        ->maxLength(35),

                    Forms\Components\TextInput::make('tipe_alat')
                        ->label('Tipe Alat')
                        ->required()
                        ->maxLength(20),

                    Forms\Components\DatePicker::make('tahun_produksi') 
                        -> label('Tahun Produksi')
                        ->displayFormat('Y') // Menampilkan hanya tahun
                        ->format('Y')        // Menyimpan hanya tahun ke dalam database
                        ->placeholder('Pilih tahun')
                        ->required(),
                    Forms\Components\TextInput::make('sta_lokasi')
                        ->label('STA Lokasi')
                        ->required()
                        ->maxLength(50),                       
                ])
                ->columns(2),

        ]);
    }

    /* =========================
     * TABLE (LIST)
     * ========================= */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('silos.nomor_silo')
                    ->label('Nomor SILO')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama_alat')
                    ->label('Nama Alat')
                    ->searchable(),

                Tables\Columns\TextColumn::make('merk_alat')
                    ->label('Merk'),

                Tables\Columns\TextColumn::make('tipe_alat')
                    ->label('Tipe'),

                Tables\Columns\TextColumn::make('tahun_produksi')
                    ->label('Tahun'),
                
                Tables\Columns\TextColumn::make('sta_lokasi')
                    ->label('STA Lokasi'),

                Tables\Columns\BadgeColumn::make('status_penggunaan')
                    ->label('Status')
                    ->getStateUsing(function ($record) {
                        return $record->assignments()
                            ->where('is_active', true)
                            ->exists()
                                ? 'Sedang Digunakan'
                                : 'Idle';
                    })
                    ->colors([
                        'warning' => 'Sedang Digunakan',
                        'secondary' => 'Idle',
                    ]),
                    
                    Tables\Columns\BadgeColumn::make('silo_status')
                        ->label('SILO')
                        ->getStateUsing(function ($record) {

                            $hasActiveSilo = \App\Models\Silos::where('alat_berat_id', $record->id)
                                ->whereDate('tanggal_expired', '>=', now())
                                ->exists();

                            return $hasActiveSilo ? 'Aktif' : 'Expired';
                        })
                        ->colors([
                            'success' => 'Aktif',
                            'danger'  => 'Expired',
                        ]),
                ])
            
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }
    

    public static function getRelations(): array
    {
        return [
            OperatorAssignmentsRelationManager::class,
        ];
    }

    /* =========================
     * PAGES
     * ========================= */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAlatBerats::route('/'),
            'create' => Pages\CreateAlatBerats::route('/create'),
            'edit'   => Pages\EditAlatBerats::route('/{record}/edit'),
        ];
    }
}