<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VentaArticulosController extends Controller
{
    public function index()
    {
        return view('venta_articulos.index');
    }
}
