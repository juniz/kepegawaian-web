<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KepegawaianAkses extends Model
{
    use HasFactory;

    protected $table = 'kepegawaian_akses';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
