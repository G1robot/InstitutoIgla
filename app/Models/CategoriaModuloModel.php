<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaModuloModel extends Model
{
    protected $table = 'categoria_modulo';
    protected $primaryKey = 'id_categoria_modulo';
    protected $fillable = ['nombre'];
    
    public function modulos()
    {
        return $this->hasMany(ModuloModel::class, 'id_categoria_modulo', 'id_categoria_modulo');
    }
}
