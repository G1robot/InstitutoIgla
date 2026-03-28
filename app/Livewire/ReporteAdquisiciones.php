<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DetalleVentaModel;
use App\Models\EstudianteModel;
use Carbon\Carbon;

class ReporteAdquisiciones extends Component
{
    public $searchEstudiante = '';
    public $estudiantesEncontrados = [];
    public $estudianteSeleccionado = null;

    public $fecha_inicio;
    public $fecha_fin;
    public $searchArticulo = '';

    // Totales para las tarjetas
    public $totalArticulos = 0;
    public $totalRecaudado = 0;

    // Lista de resultados
    public $listaAdquisiciones = [];

    public function mount()
    {
        // Por defecto: Del 1ro del año actual hasta hoy
        $this->fecha_inicio = Carbon::now()->startOfYear()->format('Y-m-d');
        $this->fecha_fin = Carbon::now()->format('Y-m-d');
    }

    // --- LÓGICA DE BÚSQUEDA DE ESTUDIANTE ---
    public function updatedSearchEstudiante()
    {
        if (strlen($this->searchEstudiante) > 2) {
            $this->estudiantesEncontrados = EstudianteModel::where('ci', 'like', '%' . $this->searchEstudiante . '%')
                ->orWhereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($this->searchEstudiante) . '%'])
                ->orWhereRaw('LOWER(apellido) LIKE ?', ['%' . strtolower($this->searchEstudiante) . '%'])
                ->take(5)
                ->get();
        } else {
            $this->estudiantesEncontrados = [];
        }
    }

    public function seleccionarEstudiante($id)
    {
        $this->estudianteSeleccionado = EstudianteModel::find($id);
        $this->searchEstudiante = '';
        $this->estudiantesEncontrados = [];
        
        // Al seleccionar, generamos el reporte de este alumno
        $this->generarReporte();
    }

    public function limpiarEstudiante()
    {
        $this->estudianteSeleccionado = null;
        $this->listaAdquisiciones = [];
        $this->totalArticulos = 0;
        $this->totalRecaudado = 0;
    }

    // --- LÓGICA DEL REPORTE ---
    public function updatedFechaInicio() { $this->generarReporte(); }
    public function updatedFechaFin() { $this->generarReporte(); }
    public function updatedSearchArticulo() { $this->generarReporte(); }

    public function generarReporte()
    {
        if (!$this->estudianteSeleccionado) {
            return; // Si no hay estudiante, no hacemos nada
        }

        if ($this->fecha_inicio > $this->fecha_fin) {
            $temp = $this->fecha_inicio;
            $this->fecha_inicio = $this->fecha_fin;
            $this->fecha_fin = $temp;
        }

        // Consulta filtrada SÓLO para el estudiante seleccionado
        $query = DetalleVentaModel::with(['venta', 'articulo.categoria'])
            ->whereHas('venta', function ($q) {
                $q->where('estado', '!=', 'anulada')
                  ->where('id_estudiante', $this->estudianteSeleccionado->id_estudiante) // <-- FILTRO CLAVE
                  ->whereDate('fecha_venta', '>=', $this->fecha_inicio)
                  ->whereDate('fecha_venta', '<=', $this->fecha_fin);
            });

        if (!empty($this->searchArticulo)) {
            $query->whereHas('articulo', function ($q) {
                $q->where('nombre', 'like', '%' . $this->searchArticulo . '%');
            });
        }

        $detalles = $query->get()->sortByDesc(function ($detalle) {
            return $detalle->venta->fecha_venta;
        });

        $this->totalArticulos = $detalles->sum('cantidad');
        $this->totalRecaudado = $detalles->sum('subtotal');
        $this->listaAdquisiciones = $detalles;
    }

    public function render()
    {
        return view('livewire.reporte-adquisiciones');
    }
}
