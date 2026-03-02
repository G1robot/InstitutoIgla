<?php

namespace App\Livewire;

use App\Models\ModuloModel;
use App\Models\CategoriaModuloModel;
use Livewire\Component;
use Livewire\WithPagination;

class Modulos extends Component
{
    use WithPagination;

    public $search = '';
    
    public $showModal = false;          
    public $showCategoriaModal = false; 

    public $modulo_id = '';
    public $nombre = '';
    public $costo = '';
    public $id_categoria_modulo = '';

    public $nueva_categoria_nombre = '';

    public function rules() {
        return [
            'nombre' => 'required|string|max:100',
            'costo' => 'required|numeric|min:0',
            'id_categoria_modulo' => 'required|exists:categoria_modulo,id_categoria_modulo',
        ];
    }

    public function render()
    {
        $search = mb_strtolower(trim($this->search));
        
        $modulos = ModuloModel::with('categoria')
            // 1. Usamos "modulo.*" en singular
            ->select('modulo.*') 
            // 2. Usamos los nombres exactos de tus tablas en la BD (modulo y categoria_modulo)
            ->leftJoin('categoria_modulo', 'modulo.id_categoria_modulo', '=', 'categoria_modulo.id_categoria_modulo')
            ->whereRaw('LOWER(modulo.nombre) LIKE ?', ["%{$search}%"])
            ->orderBy('categoria_modulo.nombre', 'asc') // Ordena por Categoría
            ->orderBy('modulo.nombre', 'asc')           // Luego ordena por Módulo
            ->paginate(10);
            
        $categorias = CategoriaModuloModel::orderBy('nombre', 'asc')->get();

        return view('livewire.modulos', compact('modulos', 'categorias'));
    }

    public function clickBuscar(){

    }

    public function openModal() {
        $this->limpiarDatos();
        $this->showModal = true;
    }

    public function closeModal() {
        $this->showModal = false;
        $this->limpiarDatos();
    }

    public function limpiarDatos() {
        $this->modulo_id = '';
        $this->nombre = '';
        $this->costo = '';
        $this->id_categoria_modulo = '';
    }

    public function guardarModulo() {
        $this->validate();

        if ($this->modulo_id) {
            $modulo = ModuloModel::find($this->modulo_id);
            $modulo->update([
                'nombre' => $this->nombre,
                'costo' => $this->costo,
                'id_categoria_modulo' => $this->id_categoria_modulo,
            ]);
        } else {
            ModuloModel::create([
                'nombre' => $this->nombre,
                'costo' => $this->costo,
                'id_categoria_modulo' => $this->id_categoria_modulo,
            ]);
        }
        $this->closeModal();

        $this->limpiarDatos();

        $this->dispatch('toast', [
            'icon' => 'success',
            'title' => 'Modulo guardado exitosamente',
        ]);
    }

    public function editar($id) {
        $modulo = ModuloModel::findOrFail($id);
        $this->modulo_id = $id;
        $this->nombre = $modulo->nombre;
        $this->costo = $modulo->costo;
        $this->id_categoria_modulo = $modulo->id_categoria_modulo;
        $this->showModal = true;
    }


    public function openCategoriaModal() {
        $this->showModal = false; 
        $this->nueva_categoria_nombre = '';
        $this->showCategoriaModal = true;
    }

    public function closeCategoriaModal() {
        $this->showCategoriaModal = false;
        $this->showModal = true;
    }

    public function guardarCategoria() {
        $this->validate(['nueva_categoria_nombre' => 'required|string|max:50|unique:categoria_modulo,nombre']);

        $cat = CategoriaModuloModel::create(['nombre' => $this->nueva_categoria_nombre]);
        
        $this->id_categoria_modulo = $cat->id_categoria_modulo;
        
        $this->closeCategoriaModal();
    }
}
