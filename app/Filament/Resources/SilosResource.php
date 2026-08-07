<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SilosResource\Pages;
use App\Models\Silos;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

class SilosResource extends Resource
{
    protected static ?string $model = Silos::class;

    protected static ?string $navigationLabel = 'SILO';

    protected static ?string $pluralModelLabel = 'SILO';

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Dokumen SILO')
                ->schema([

                    // Pilih alat berat (pakai KODE ALAT)
                    Select::make('alat_berat_id')
                        ->label('Alat Berat')
                        ->relationship('alatBerat', 'kode_alat')
                        ->searchable()
                        ->preload()
                        ->required(),

                    // Nomor dokumen SILO
                    TextInput::make('nomor_silo')
                        ->label('Nomor SILO')
                        ->required(),

                    // Tanggal terbit
                    DatePicker::make('tanggal_terbit')
                        ->label('Tanggal Terbit')
                        ->required(),

                    // Upload file SILO
                    FileUpload::make('file_path')
                        ->label('File SILO')
                        ->disk('local')
                        ->directory('silo')
                        ->visibility('private')
                        // 'local' disk has no public URL; resolve the existing
                        // file's preview through the authenticated document
                        // route instead (Phase 0 / Milestone M0.3).
                        ->getUploadedFileUrlUsing(fn (?string $file, $record): ?string => ($file && $record)
                            ? route('documents.silo.show', $record)
                            : null)
                        ->acceptedFileTypes(['application/pdf', 'image/*'])
                        ->maxSize(2048)
                        ->imagePreviewHeight('300')
                        ->helperText('Kosongkan jika tidak ingin mengganti file')
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // Kode alat (identitas aset)
                TextColumn::make('alatBerat.kode_alat')
                    ->label('Kode Alat')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('alatBerat.nama_alat')
                    ->label('Nama Alat')
                    ->searchable()
                    ->sortable(),

                // Nomor SILO (dokumen)
                TextColumn::make('nomor_silo')
                    ->label('Nomor SILO')
                    ->searchable(),

                // Tanggal terbit
                TextColumn::make('tanggal_terbit')
                    ->label('Terbit')
                    ->date()
                    ->sortable(),

                // STATUS DOKUMEN
                BadgeColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn ($record) => $record->statusLabel())
                    ->colors([
                        'danger' => 'Expired',
                        'warning' => 'Akan Expired',
                        'success' => 'Aktif',
                    ]),

                ImageColumn::make('file_path')
                    ->label('File')
                    // 'local' disk has no public URL; render the thumbnail
                    // through the authenticated document route instead
                    // (Phase 0 / Milestone M0.3).
                    ->getStateUsing(fn ($record): ?string => $record->file_path
                        ? route('documents.silo.show', $record)
                        : null)
                    ->height(50),

                TextColumn::make('file_link')
                    ->label('Lihat File')
                    ->getStateUsing(fn ($record) => 'Buka')
                    ->url(fn ($record) => $record->file_path ? route('documents.silo.show', $record) : null)
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-eye'),
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
            'index' => Pages\ListSilos::route('/'),
            'create' => Pages\CreateSilos::route('/create'),
            'edit' => Pages\EditSilos::route('/{record}/edit'),
        ];
    }
}
