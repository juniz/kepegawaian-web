<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanCuti extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_cuti';
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
        'alamat',
        'kepentingan',
        'jumlah',
        'nik_pj',
        'status',
    ];
}
