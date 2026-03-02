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

    // NUEVO: Inscripciones a Módulos sueltos
    public function inscripcionesModulos()
    {
        return $this->hasMany(InscripcionModuloModel::class, 'id_estudiante', 'id_estudiante');
    }

    // NUEVO: Derechos adquiridos (Para verificar PUP)
    public function derechos()
    {
        return $this->hasMany(EstudianteDerechoModel::class, 'id_estudiante', 'id_estudiante');
    }

    // NUEVO: Todos los pagos (Global)
    public function pagos()
    {
        return $this->hasMany(PagoModel::class, 'id_estudiante', 'id_estudiante');
    }
}
