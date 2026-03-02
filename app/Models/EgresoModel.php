<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EgresoModel extends Model
{
    protected $table = 'egresos';
    protected $primaryKey = 'id_egreso';
    protected $fillable = [
        'id_proveedor', 
        'id_metodo_pago', // Importante para el arqueo
        'concepto', 
        'descripcion', 
        'monto', 
        'fecha_egreso', 
        'nro_factura', 
        'tipo_comprobante',
        'id_caja' // Para relacionar con la caja abierta en ese momento
    ];

    public function caja()
    {
        return $this->belongsTo(CajaModel::class, 'id_caja');
    }

    public function proveedor()
    {
        return $this->belongsTo(ProveedorModel::class, 'id_proveedor');
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPagoModel::class, 'id_metodo_pago');
    }
}
