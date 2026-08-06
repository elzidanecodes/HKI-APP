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
    public function assignments()
    {
        return $this->hasMany(OperatorAlatAssignment::class);
    }

    // Assignment aktif
    public function activeAssignment()
    {
        return $this->hasOne(OperatorAlatAssignment::class)
            ->where('is_active', true);
    }
}