<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoriasModulosController extends Controller
{
    public function index()
    {
        return view('categorias_modulos.index');
    }
}
