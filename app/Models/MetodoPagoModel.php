<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetodoPagoModel extends Model
{
    protected $table = 'metodos_pago';
    protected $primaryKey = 'id_metodo_pago';
    protected $fillable = ['nombre', 'es_efectivo', 'activo'];
}
