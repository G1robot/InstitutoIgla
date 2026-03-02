<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanModel extends Model
{
    protected $table = 'plan';
    protected $primaryKey = 'id_plan';
    protected $fillable = [
        'nombre',
        'duracion_anios',
        'duracion_meses',
        'costo_anual',
        'costo_mensual',
        'tipo_pago',
    ];
}
