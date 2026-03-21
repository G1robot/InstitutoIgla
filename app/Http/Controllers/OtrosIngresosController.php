<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OtrosIngresosController extends Controller
{
    public function index()
    {
        return view('otros_ingresos.index');
    }
}
