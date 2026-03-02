<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarifaModel extends Model
{
    protected $table = 'tarifas';
    protected $primaryKey = 'id_tarifa';
    protected $fillable = [
        'codigo',   // Ej: 'PUA', 'PUP'
        'monto',
        'gestion',  // Año, o null si es permanente
    ];
    
    // Si quieres ver cuántos pagos se han hecho de esta tarifa
    public function pagos()
    {
        return $this->morphMany(PagoModel::class, 'origen');
    }
}
