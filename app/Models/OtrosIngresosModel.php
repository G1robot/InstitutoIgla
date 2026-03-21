<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtrosIngresosModel extends Model
{
    protected $table = 'otros_ingresos';
    protected $primaryKey = 'id_ingreso';
    protected $fillable = [
        'nombre_origen',
        'concepto',
        'descripcion',
        'monto_total',
        'fecha_ingreso'
    ];
}
