<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArqueoModel extends Model
{
    protected $table = 'arqueos';
    protected $primaryKey = 'id_arqueo';
    protected $fillable = [
        'fecha_arqueo', 
        'monto_inicial', 
        'total_ingresos', 
        'total_egresos', 
        'saldo_sistema', 
        'saldo_real', 
        'diferencia', 
        'observaciones'
    ];
}
