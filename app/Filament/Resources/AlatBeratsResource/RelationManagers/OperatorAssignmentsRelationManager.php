<?php

namespace App\Filament\Resources\AlatBeratsResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use App\Models\OperatorAlatAssignment;
use App\Models\Sios;

class OperatorAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';

    protected static ?string $recordTitleAttribute = 'tanggal_mulai';


    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('operator_id')
                ->label('Operator')
                ->relationship(
                    'operator',
                    'nama_operator',
                    fn ($query) =>
                        $query->whereHas('sio', fn ($q) =>
                            $q->whereDate('tanggal_expired', '>=', now())
                        )
                )
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\DatePicker::make('tanggal_mulai')
                ->label('Tanggal Mulai')
                ->default(now())
                ->required(),
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('operator.nama_operator')
                    ->label('Operator'),

                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Mulai')
                    ->date(),

                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Selesai')
                    ->date()
                    ->placeholder('-'),

                Tables\Columns\BadgeColumn::make('is_active')
                    ->label('Status')
                    ->getStateUsing(fn ($record) => $record->is_active ? 'Aktif' : 'Selesai')
                    ->colors([
                        'success' => 'Aktif',
                        'secondary' => 'Selesai',
                    ]),
            ])

            /* ================= HEADER ACTION ================= */
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Assign Operator')

                    ->disabled(function (RelationManager $livewire) {
                        $alatBerat = $livewire->getOwnerRecord();

                        return
                            $alatBerat->activeAssignment()->exists()
                            || ! $alatBerat->hasActiveSilo();
                    })

                    ->before(function (array $data, RelationManager $livewire, $action) {
                        $alatBerat = $livewire->getOwnerRecord();

                        // SILO harus aktif
                        if (! $alatBerat->hasActiveSilo()) {
                            Notification::make()
                                ->title('SILO Tidak Aktif')
                                ->body('Alat berat ini memiliki SILO yang sudah expired.')
                                ->danger()
                                ->send();

                            $action->halt();
                        }

                        // Alat tidak boleh sedang digunakan
                        if ($alatBerat->activeAssignment()->exists()) {
                            Notification::make()
                                ->title('Alat Sedang Digunakan')
                                ->danger()
                                ->send();

                            $action->halt();
                        }

                        // Operator tidak boleh aktif di alat lain
                        if (
                            OperatorAlatAssignment::where('operator_id', $data['operator_id'])
                                ->where('is_active', true)
                                ->exists()
                        ) {
                            Notification::make()
                                ->title('Operator Masih Bertugas')
                                ->danger()
                                ->send();

                            $action->halt();
                        }

                        // SIO operator harus aktif
                        if (
                            ! Sios::where('operator_id', $data['operator_id'])
                                ->whereDate('tanggal_expired', '>=', now())
                                ->exists()
                        ) {
                            Notification::make()
                                ->title('SIO Operator Tidak Aktif')
                                ->danger()
                                ->send();

                            $action->halt();
                        }
                    })

                    ->mutateFormDataUsing(function (array $data, RelationManager $livewire) {
                        return [
                            ...$data,
                            'alat_berat_id' => $livewire->getOwnerRecord()->id,
                            'is_active' => true,
                        ];
                    }),
            ])

            /* ================= ROW ACTION ================= */
            ->actions([
                Tables\Actions\Action::make('end')
                    ->label('End')
                    ->color('danger')
                    ->visible(fn ($record) => $record->is_active)
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function ($record, array $data, RelationManager $livewire) {
                        $record->update([
                            'tanggal_selesai' => $data['tanggal_selesai'],
                            'is_active' => false,
                        ]);

                        $livewire->getOwnerRecord()->refresh();
                    }),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->orderByDesc('is_active')
            ->orderByDesc('tanggal_mulai');
    }
}