<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OperatorAlatAssignment;
use App\Models\Sios;
use App\Models\Silos;
use Carbon\Carbon;

class AutoEndExpiredAssignments extends Command
{
    protected $signature = 'assignment:auto-end-expired';
    protected $description = 'Auto end assignment jika SIO atau SILO expired';

    public function handle()
    {
        $today = Carbon::today();

        $activeAssignments = OperatorAlatAssignment::where('is_active', true)->get();

        foreach ($activeAssignments as $assignment) {

            // 🔴 cek SIO operator
            $sioExpired = Sios::where('operator_id', $assignment->operator_id)
                ->whereDate('tanggal_expired', '<', $today)
                ->exists();

            // 🔴 cek SILO alat
            $siloExpired = Silos::where('alat_berat_id', $assignment->alat_berat_id)
                ->whereDate('tanggal_expired', '<', $today)
                ->exists();

            if ($sioExpired || $siloExpired) {
                $assignment->update([
                    'is_active' => false,
                    'tanggal_selesai' => $today,
                ]);

                $this->info("Assignment {$assignment->id} di-end otomatis");
            }
        }

        return Command::SUCCESS;
    }
}