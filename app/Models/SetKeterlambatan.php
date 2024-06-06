<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SetKeterlambatan extends Model
{
    use HasFactory;

    protected $table = 'set_keterlambatan';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;
}
