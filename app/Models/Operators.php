<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Operators extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'nama_operator',
        'nomor_hp',
    ];

    // Relasi ke SIO (dokumen)
    public function sio()
    {
        return $this->hasMany(Sios::class, 'operator_id');
    }

    
    public function activeSio()
    {
        return $this->hasOne(Sios::class, 'operator_id')
            ->where('is_active', true);
    }

    // Relasi ke assignment
    //
    // Regression fix (TECHNICAL_AUDIT.md M2, IMPLEMENTATION_PLAN.md
    // Milestone M5.1): without an explicit FK, Laravel infers one from
    // this model's (plural) class name — "operators_id" — instead of the
    // real "operator_id" column, so these relations were previously
    // latent-broken. Explicit FK matches the pattern AlatBerats.php
    // already uses.
    public function assignments()
    {
        return $this->hasMany(OperatorAlatAssignment::class, 'operator_id', 'id');
    }

    // Assignment aktif
    public function activeAssignment()
    {
        return $this->hasOne(OperatorAlatAssignment::class, 'operator_id', 'id')
            ->where('is_active', true);
    }
}