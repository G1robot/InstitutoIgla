<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InscripcionModuloModel extends Model
{
    protected $table = 'inscripcion_modulo';
    protected $primaryKey = 'id_inscripcion_modulo';
    protected $fillable = [
        'id_estudiante',
        'id_modulo',
        'fecha_inscripcion',
        'estado',
        'costo_al_momento',
    ];

    public function estudiante()
    {
        return $this->belongsTo(EstudianteModel::class, 'id_estudiante', 'id_estudiante');
    }

    public function modulo()
    {
        return $this->belongsTo(ModuloModel::class, 'id_modulo', 'id_modulo');
    }

    // Esta inscripción a módulo genera un pago en la tabla pagos
    public function pagos()
    {
        return $this->morphMany(PagoModel::class, 'origen');
    }
}
