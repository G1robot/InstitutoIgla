<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticuloModel extends Model
{
    protected $table = 'articulos';
    protected $primaryKey = 'id_articulo';
    protected $fillable = [
        'id_categoria_articulo', 
        'nombre', 
        'precio', 
        'stock', 
        'es_obligatorio'
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaArticuloModel::class, 'id_categoria_articulo');
    }
}
