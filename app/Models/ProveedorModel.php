<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProveedorModel extends Model
{
    protected $table = 'proveedores';
    protected $primaryKey = 'id_proveedor';
    protected $fillable = ['nombre_empresa', 'nombre_contacto', 'telefono', 'nit_ci'];

    public function egresos()
    {
        return $this->hasMany(EgresoModel::class, 'id_proveedor');
    }
}
