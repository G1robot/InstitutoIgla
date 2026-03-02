<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\EstudianteModel;
use App\Models\InscripcionModuloModel;
use App\Models\PagoModel;

class HistorialModulos extends Component
{
    public $search = '';
    public $estudianteSeleccionado = null;
    public $modulos = [];

    // Para la búsqueda predictiva
    public $estudiantesEncontrados = [];

    public function updatedSearch()
    {
        if (strlen($this->search) > 2) {
            $this->estudiantesEncontrados = EstudianteModel::where('ci', 'like', '%' . $this->search . '%')
                ->orWhereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($this->search) . '%'])
                ->orWhereRaw('LOWER(apellido) LIKE ?', ['%' . strtolower($this->search) . '%'])
                ->take(5)
                ->get();
        } else {
            $this->estudiantesEncontrados = [];
        }
    }

    public function seleccionarEstudiante($id)
    {
        $this->estudianteSeleccionado = EstudianteModel::find($id);
        $this->search = '';
        $this->estudiantesEncontrados = [];
        $this->cargarModulos();
    }

    public function cargarModulos()
    {
        if ($this->estudianteSeleccionado) {
            // Traemos la inscripción con el módulo y sus pagos
            $this->modulos = InscripcionModuloModel::with(['modulo', 'pagos'])
                ->where('id_estudiante', $this->estudianteSeleccionado->id_estudiante)
                ->orderBy('fecha_inscripcion', 'desc')
                ->get();
        }
    }

    public function marcarFinalizado($idInscripcionModulo)
    {
        $inscripcion = InscripcionModuloModel::find($idInscripcionModulo);
        if ($inscripcion) {
            $inscripcion->estado = 'finalizado';
            $inscripcion->save();
            
            // Recargamos la lista
            $this->cargarModulos(); 
            session()->flash('success', 'El módulo se ha marcado como FINALIZADO.');
        }
    }
    
    public function reactivarModulo($idInscripcionModulo)
    {
        $inscripcion = InscripcionModuloModel::find($idInscripcionModulo);
        if ($inscripcion) {
            $inscripcion->estado = 'cursando';
            $inscripcion->save();
            $this->cargarModulos();
        }
    }
    
    public function render()
    {
        return view('livewire.historial-modulos');
    }
}
