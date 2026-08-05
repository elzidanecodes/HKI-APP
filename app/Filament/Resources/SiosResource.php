<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiosResource\Pages;
use App\Models\Sios;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;

use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;

class SiosResource extends Resource
{
    protected static ?string $model = Sios::class;

    protected static ?string $navigationLabel = 'SIO';
    protected static ?string $pluralModelLabel = 'SIO';

    protected static ?string $navigationIcon = 'heroicon-o-collection';
    
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Dokumen SIO')
                ->schema([

                    Select::make('operator_id')
                        ->label('Operator')
                        ->relationship('operator', 'nama_operator')
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('nomor_sio')
                        ->label('Nomor SIO')
                        ->required(),

                    DatePicker::make('tanggal_expired')
                        ->label('Berlaku Sampai')
                        ->required(),

                    FileUpload::make('file_sio')
                        ->label('File SIO')
                        ->disk('local')
                        ->directory('sio')
                        ->visibility('private')
                        // 'local' disk has no public URL; resolve the existing
                        // file's preview through the authenticated document
                        // route instead (Phase 0 / Milestone M0.3).
                        ->getUploadedFileUrlUsing(fn (?string $file, $record): ?string => ($file && $record)
                            ? route('documents.sio.show', $record)
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

                TextColumn::make('operator.nama_operator')
                    ->label('Operator')
                    ->searchable(),

                TextColumn::make('nomor_sio')
                    ->label('Nomor SIO'),

                TextColumn::make('tanggal_expired')
                    ->label('Berlaku Sampai')
                    ->date(),

               BadgeColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function ($record) {

                        $today = Carbon::today();
                        $expired = Carbon::parse($record->tanggal_expired)->startOfDay();

                        // SUDAH EXPIRED
                        if ($expired->lt($today)) {
                            return 'Expired';
                        }

                        // AKAN EXPIRED (antara hari ini s/d 30 hari ke depan)
                        if ($expired->between(
                            $today,
                            $today->copy()->addDays(30),
                            true // inclusive
                        )) {
                            return 'Akan Expired';
                        }

                        // MASIH AKTIF
                        return 'Aktif';
                    })
                    ->colors([
                        'danger'  => 'Expired',
                        'warning' => 'Akan Expired',
                        'success' => 'Aktif',
                    ]),

                ImageColumn::make('file_sio')
                    ->label('File')
                    // 'local' disk has no public URL; render the thumbnail
                    // through the authenticated document route instead
                    // (Phase 0 / Milestone M0.3).
                    ->getStateUsing(fn ($record): ?string => $record->file_sio
                        ? route('documents.sio.show', $record)
                        : null)
                    ->height(50),

                TextColumn::make('file_link')
                    ->label('Lihat File')
                    ->getStateUsing(fn ($record) => 'Buka')
                    ->url(fn ($record) => $record->file_sio ? route('documents.sio.show', $record) : null)
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-eye'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListSios::route('/'),
            'create' => Pages\CreateSios::route('/create'),
            'edit' => Pages\EditSios::route('/{record}/edit'),
        ];
    }    
}
