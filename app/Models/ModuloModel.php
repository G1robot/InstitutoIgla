<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuloModel extends Model
{
    protected $table = 'modulo';
    protected $primaryKey = 'id_modulo';
    protected $fillable = [
        'nombre',
        'costo',
        'id_categoria_modulo',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaModuloModel::class, 'id_categoria_modulo', 'id_categoria_modulo');
    }
}
