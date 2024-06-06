<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapPresensi extends Model
{
    use HasFactory;

    protected $table = 'rekap_presensi';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'shift',
        'jam_datang',
        'jam_pulang',
        'status',
        'keterlambatan',
        'keterangan',
        'durasi',
        'photo',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id');
    }
}
