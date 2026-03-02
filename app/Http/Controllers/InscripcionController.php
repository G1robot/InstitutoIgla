<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InscripcionController extends Controller
{
    public function index(){
        return view('inscripciones.index');
    }
}
