<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControlInsumoModel extends Model
{
    use HasFactory;

    protected $table = 'control_insumos';

    protected $primaryKey = 'id_control_insumo';

    protected $fillable = [
        'id_estudiante',
        'fecha_semana',
        'estado',
        'id_venta',
        'observacion'
    ];

    protected $casts = [
        'fecha_semana' => 'date',
    ];


    public function estudiante()
    {
        return $this->belongsTo(EstudianteModel::class, 'id_estudiante', 'id_estudiante');
    }

    public function venta()
    {
        return $this->belongsTo(VentaModel::class, 'id_venta', 'id_venta');
    }
}
