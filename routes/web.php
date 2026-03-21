<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TarifaController;
use App\Http\Controllers\ModuloController;
use App\Http\Controllers\InscripcionModuloController;
use App\Http\Controllers\HistorialModulosController;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\VentaArticulosController;
use App\Http\Controllers\EgresoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\CategoriasModulosController;
use App\Http\Controllers\OtrosIngresosController;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth:web'])->group(function () {
    Route::get('/',[HomeController::class,'index'])->name('home');
    Route::get('/planes',[PlanController::class,'index'])->name('planes');
    Route::get('/estudiantes',[EstudianteController::class,'index'])->name('estudiantes');
    Route::get('/inscripciones',[InscripcionController::class,'index'])->name('inscripciones');
    Route::get('/pagos',[PagoController::class,'index'])->name('pagos');
    Route::get('/usuarios',[UsuarioController::class,'index'])->name('usuarios');
    Route::get('/tarifas',[TarifaController::class,'index'])->name('tarifas');
    Route::get('/modulos',[ModuloController::class,'index'])->name('modulos');
    Route::get('/inscripcion-modulo',[InscripcionModuloController::class,'index'])->name('inscripcion-modulo');
    Route::get('/historial-modulos',[HistorialModulosController::class,'index'])->name('historial-modulos');
    Route::get('/articulos',[ArticuloController::class,'index'])->name('articulos');
    Route::get('/venta-articulos',[VentaArticulosController::class,'index'])->name('venta-articulos');
    Route::get('/egresos',[EgresoController::class,'index'])->name('egresos');
    Route::get('/reporte-arqueo',[ReporteController::class,'arqueo'])->name('reporte-arqueo');
    Route::get('/reporte-ingresos',[ReporteController::class,'ingresos'])->name('reporte-ingresos');
    Route::get('/reporte-egresos',[ReporteController::class,'egresos'])->name('reporte-egresos');
    Route::get('/categorias-modulos',[CategoriasModulosController::class,'index'])->name('categorias-modulos');
    Route::get('/otros-ingresos',[OtrosIngresosController::class,'index'])->name('otros-ingresos');
});