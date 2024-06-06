<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JamJaga extends Model
{
    use HasFactory;

    protected $table = 'jam_jaga';
    protected $primaryKey = 'no_id';
    public $incrementing = false;
    public $timestamps = false;
}
