<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaArticuloModel extends Model
{
    protected $table = 'categorias_articulo';
    protected $primaryKey = 'id_categoria_articulo';
    protected $fillable = ['nombre'];

    public function articulos()
    {
        return $this->hasMany(ArticuloModel::class, 'id_categoria_articulo');
    }
}
