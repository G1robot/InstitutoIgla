<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InscripcionModuloController extends Controller
{
    public function index(){
        return view('inscripcion_modulo.index');
    }
}
