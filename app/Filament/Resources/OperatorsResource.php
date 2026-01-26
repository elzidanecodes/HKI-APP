<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OperatorsResource\Pages;
use App\Models\Silos;
use App\Models\Operators;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Resources\Table;

class OperatorsResource extends Resource
{
    protected static ?string $model = Operators::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Operator';
    protected static ?string $pluralModelLabel = 'Operator';

    /* =========================
     * FORM (FILAMENT v2)
     * ========================= */
    public static function form(Form $form): Form
    {
        return $form->schema([

            // =========================
            // DATA OPERATOR
            // =========================
            Forms\Components\Section::make('Data Operator')
                ->schema([
                    Forms\Components\TextInput::make('nama_operator')
                        ->label('Nama Operator')
                        ->required()
                        ->maxLength(50),

                    Forms\Components\TextInput::make('nomor_hp')
                        ->label('Nomor HP')
                        ->required()
                        ->maxLength(15),
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
                Tables\Columns\TextColumn::make('nama_operator')
                    ->label('Nama Operator')
                    ->searchable(),

                Tables\Columns\TextColumn::make('activeSio.nomor_sio')
                    ->label('Nomor SIO')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('nomor_hp')
                    ->label('No HP'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    /* =========================
     * PAGES
     * ========================= */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOperators::route('/'),
            'create' => Pages\CreateOperators::route('/create'),
            'view'   => Pages\ViewOperators::route('/{record}'),
            'edit'   => Pages\EditOperators::route('/{record}/edit'),
        ];
    }
}