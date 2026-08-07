<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatorAlatAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator_id',
        'alat_berat_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
    ];

    // TECHNICAL_AUDIT.md M8 / IMPLEMENTATION_PLAN.md Milestone M5.1:
    // these were plain strings, inconsistent with Sios/Silos, which cast
    // their date columns.
    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function operator()
    {
        return $this->belongsTo(Operators::class);
    }

    public function alatBerat()
    {
        return $this->belongsTo(AlatBerats::class, 'alat_berat_id', 'id');
    }
}
