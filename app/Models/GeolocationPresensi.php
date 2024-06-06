<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeolocationPresensi extends Model
{
    use HasFactory;

    protected $table = 'geolocation_presensi';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;
}
