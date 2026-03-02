<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TurnoModel extends Model
{
    protected $table = 'turno';
    protected $primaryKey = 'id_turno';
    protected $fillable = ['nombre'];
}
