<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ControlInsumosController extends Controller
{
    public function index(){
        return view('control_insumos.index');
    }
}
