<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CajaModel extends Model
{
    protected $table = 'caja';
    protected $primaryKey = 'id_caja';
    protected $fillable = [
        'id_usuario', 'id_turno', 'fecha_apertura', 'fecha_cierre', 
        'monto_inicial', 'monto_final_sistema', 'monto_final_fisico', 
        'diferencia', 'estado', 'observaciones'
    ];

    public function usuario()
    {
        return $this->belongsTo(UsuarioModel::class, 'id_usuario');
    }

    public function turno()
    {
        return $this->belongsTo(TurnoModel::class, 'id_turno');
    }
}
