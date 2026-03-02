<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TarifaController extends Controller
{
    public function index(){
        return view('tarifas.index');
    }
}
