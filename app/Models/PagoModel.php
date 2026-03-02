<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoModel extends Model
{
    protected $table = 'pagos';
    protected $primaryKey = 'id_pago';
    protected $fillable = [
        'origen_id',      // ID de Inscripcion, Modulo, o Tarifa
        'origen_type',    // El modelo (App\Models\InscripcionModel, etc)
        'id_estudiante',
        'fecha_vencimiento',
        'fecha_pago',
        'descripcion',
        'monto_total',
        'monto_abonado',  // Tu monto_reserva
        'estado',         // pendiente, parcial, pagado, vencido
    ];

    public function estudiante()
    {
        return $this->belongsTo(EstudianteModel::class, 'id_estudiante', 'id_estudiante');
    }

    // RELACIÓN POLIMÓRFICA
    // Esto detecta automáticamente si el pago viene de un Plan, Modulo o Tarifa
    public function origen()
    {
        return $this->morphTo(__FUNCTION__, 'origen_type', 'origen_id');
    }

    public function transacciones()
    {
        return $this->hasMany(TransaccionModel::class, 'id_pago');
    }

    // Helper para ver desglose (opcional)
    public function getDesglosePagoAttribute()
    {
        return $this->transacciones->map(function($t) {
            return "{$t->metodo->nombre}: {$t->monto}";
        })->join(', ');
    }
    
    // public function inscripcion()
    // {
    //     return $this->belongsTo(InscripcionModel::class, 'id_inscripcion');
    // }
}
