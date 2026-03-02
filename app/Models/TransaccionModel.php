<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaccionModel extends Model
{
    protected $table = 'transacciones';
    protected $primaryKey = 'id_transaccion';
    protected $fillable = ['id_pago', 'id_metodo_pago', 'monto', 'fecha_transaccion', 'id_caja'];

    public function metodo()
    {
        return $this->belongsTo(MetodoPagoModel::class, 'id_metodo_pago');
    }

    public function caja()
    {
        return $this->belongsTo(CajaModel::class, 'id_caja');
    }

    public function pago()
    {
        // Una transacción pertenece a un pago principal
        return $this->belongsTo(PagoModel::class, 'id_pago');
    }
}
