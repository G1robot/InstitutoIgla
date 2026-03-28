<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function arqueo()
    {
        return view('reportes.arqueo');
    }

    public function ingresos()
    {
        return view('reportes.ingresos');
    }

    public function egresos()
    {
        return view('reportes.egresos');
    }

    public function adquisiciones()
    {
        return view('reportes.adquisiciones');
    }
}
