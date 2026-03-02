<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EgresoController extends Controller
{
    public function index()
    {
        return view('egresos.index');
    }
}
