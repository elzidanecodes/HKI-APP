<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Sios extends Model
{
    use HasFactory;

    protected $table = 'sios';

    protected $fillable = [
        'operator_id',
        'nomor_sio',
        'tanggal_terbit',
        'tanggal_expired',
        'file_sio',
        'is_active',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
        'tanggal_expired' => 'date',
        'is_active' => 'boolean',
    ];

    // Relasi ke operator
    public function operator()
    {
        return $this->belongsTo(Operators::class);
    }

    // Helper: cek expired
    public function isExpired(): bool
    {
        return $this->tanggal_expired->isPast();
    }

    // Helper: cek masih berlaku
    public function isValid(): bool
    {
        return ! $this->isExpired();
    }
}