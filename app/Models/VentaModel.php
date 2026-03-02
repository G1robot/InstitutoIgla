<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaModel extends Model
{
    protected $table = 'ventas';
    protected $primaryKey = 'id_venta';
    protected $fillable = ['id_estudiante', 'fecha_venta', 'monto_total', 'estado'];

    public function estudiante()
    {
        return $this->belongsTo(EstudianteModel::class, 'id_estudiante');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVentaModel::class, 'id_venta');
    }

    public function pago()
    {
        return $this->morphOne(PagoModel::class, 'origen');
    }
}
