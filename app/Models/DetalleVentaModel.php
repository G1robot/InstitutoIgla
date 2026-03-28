<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVentaModel extends Model
{
    protected $table = 'detalle_ventas';
    protected $primaryKey = 'id_detalle_venta';
    protected $fillable = [
        'id_venta', 
        'id_articulo', 
        'cantidad', 
        'precio_unitario', 
        'subtotal'
    ];

    public function venta()
    {
        return $this->belongsTo(VentaModel::class, 'id_venta', 'id_venta');
    }

    public function articulo()
    {
        return $this->belongsTo(ArticuloModel::class, 'id_articulo', 'id_articulo');
    }
}
