<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InscripcionModel extends Model
{
    protected $table = 'inscripcion';
    protected $primaryKey = 'id_inscripcion';
    protected $fillable = [
        'id_estudiante',
        'id_plan',
        'id_turno',
        'gestion_inicio',
        'anio_actual',
        'fecha_inscripcion',
        'estado',
    ];

    public function estudiante()
    {
        return $this->belongsTo(EstudianteModel::class, 'id_estudiante', 'id_estudiante');
    }
    public function plan()
    {
        return $this->belongsTo(PlanModel::class, 'id_plan', 'id_plan');
    }
    public function pagos()
    {
        return $this->morphMany(PagoModel::class, 'origen');
    }
    public function turno()
    {
        return $this->belongsTo(TurnoModel::class, 'id_turno', 'id_turno');
    }
}
