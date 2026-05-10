<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstudianteModel extends Model
{
    protected $table = 'estudiante';
    protected $primaryKey = 'id_estudiante';
    protected $fillable = [
        'nombre',
        'apellido',
        'ci',
        'telefono',
        'fecha_nacimiento',
        'genero',
    ];
    public function inscripciones()
    {
        return $this->hasMany(InscripcionModel::class, 'id_estudiante', 'id_estudiante');
    }

    public function inscripcionesModulos()
    {
        return $this->hasMany(InscripcionModuloModel::class, 'id_estudiante', 'id_estudiante');
    }

    public function derechos()
    {
        return $this->hasMany(EstudianteDerechoModel::class, 'id_estudiante', 'id_estudiante');
    }

    public function pagos()
    {
        return $this->hasMany(PagoModel::class, 'id_estudiante', 'id_estudiante');
    }

    public function controlInsumos()
    {
        return $this->hasMany(ControlInsumoModel::class, 'id_estudiante', 'id_estudiante');
    }
}
