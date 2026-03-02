<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HistorialModulosController extends Controller
{
    public function index()
    {
        return view('historial_modulos.index');
    }
}
