<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstudianteDerechoModel extends Model
{
    protected $table = 'estudiante_derechos';
    // Laravel asume 'id' por defecto, pero si usaste otro ID en la migración ajustalo
    protected $fillable = [
        'id_estudiante',
        'derecho',          // 'PUP_PAGADO'
        'fecha_adquisicion',
    ];

    public function estudiante()
    {
        return $this->belongsTo(EstudianteModel::class, 'id_estudiante', 'id_estudiante');
    }
}
