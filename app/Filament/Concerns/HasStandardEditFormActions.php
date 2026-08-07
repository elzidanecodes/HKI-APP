<?php

namespace App\Filament\Concerns;

use Filament\Pages\Actions\ButtonAction;

/**
 * TECHNICAL_AUDIT.md Duplikasi #2 / IMPLEMENTATION_PLAN.md Milestone
 * M5.1: this "Perbarui Data"/"Batal" form action pair was duplicated
 * byte-for-byte across every Edit page (AlatBerats, Operators, Silos,
 * Sios, Logistiks) — justified here by duplication that already existed
 * in ≥2 places (Blueprint P1), the same justification already accepted
 * for App\Policies\Concerns\LogsPolicyDecisions.
 *
 * Create pages (CreateOperators, CreateLogistiks) are deliberately not
 * included: their form actions use a different label ("Simpan Data
 * Baru") and submit target ('store'), so they are not this duplication.
 */
trait HasStandardEditFormActions
{
    protected function getFormActions(): array
    {
        return [
            ButtonAction::make('submit')
                ->label('Perbarui Data')
                ->action('save')
                ->color('primary')
                ->submit('update'),

            ButtonAction::make('cancel')
                ->label('Batal')
                ->url($this->getResource()::getUrl('index'))
                ->color('secondary'),
        ];
    }
}
