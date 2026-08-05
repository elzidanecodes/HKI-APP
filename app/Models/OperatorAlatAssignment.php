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

    public function operator()
    {
        return $this->belongsTo(Operators::class);
    }

    public function alatBerat()
    {
        return $this->belongsTo(AlatBerats::class, 'alat_berat_id', 'id');
    }
}
