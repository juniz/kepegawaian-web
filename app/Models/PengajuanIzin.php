<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanIzin extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_izin';
    protected $primaryKey = 'no_pengajuan';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_pengajuan',
        'tanggal',
        'tanggal_awal',
        'tanggal_akhir',
        'nik',
        'urgensi',
        'kepentingan',
        'jumlah',
        'nik_pj',
        'status',
    ];
}
