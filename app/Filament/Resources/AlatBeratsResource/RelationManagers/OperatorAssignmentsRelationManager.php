<?php

namespace App\Filament\Resources\AlatBeratsResource\RelationManagers;

use App\Application\Operations\AssignOperator;
use App\Application\Operations\EndAssignment;
use App\Models\Operators;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

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
                    fn ($query) => $query->whereHas('sio', fn ($q) => $q->currentlyValid())
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

                    ->before(function (array $data, RelationManager $livewire, $action, AssignOperator $assignOperator) {
                        // Single source of truth for the guard
                        // (TECHNICAL_AUDIT.md §7 "Business Logic —
                        // Terfragmentasi"; IMPLEMENTATION_PLAN.md M2.3/M2.5).
                        // Only the check is delegated here — the actual
                        // write still goes through mutateFormDataUsing()
                        // below and Filament's own create(), unchanged, to
                        // avoid inserting the record twice.
                        //
                        // $assignOperator is container-injected by Filament's
                        // evaluate() mechanism (it resolves any type-hinted
                        // class), not constructed here — this file must not
                        // import App\Domain\* directly except enums/value
                        // objects (ARCHITECTURE_BLUEPRINT.md §8.2), and
                        // AssignmentEligibility is a domain service, not
                        // either of those.
                        $alatBerat = $livewire->getOwnerRecord();
                        $operator = Operators::findOrFail($data['operator_id']);

                        $reasons = $assignOperator->checkEligibility($alatBerat, $operator, now());

                        $messages = [
                            'equipment_silo_not_active' => ['SILO Tidak Aktif', 'Alat berat ini memiliki SILO yang sudah expired.'],
                            'equipment_already_assigned' => ['Alat Sedang Digunakan', null],
                            'operator_already_assigned_elsewhere' => ['Operator Masih Bertugas', null],
                            'operator_sio_not_active' => ['SIO Operator Tidak Aktif', null],
                        ];

                        foreach ($reasons as $reason) {
                            [$title, $body] = $messages[$reason];

                            $notification = Notification::make()->title($title)->danger();

                            if ($body !== null) {
                                $notification->body($body);
                            }

                            $notification->send();
                        }

                        if ($reasons !== []) {
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
                        (new EndAssignment)->handle($record, Carbon::parse($data['tanggal_selesai']));

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
