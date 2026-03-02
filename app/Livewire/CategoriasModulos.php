<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CategoriaModuloModel;
use App\Models\ModuloModel; // Importamos para comprobar si está en uso
use Illuminate\Validation\Rule;

class CategoriasModulos extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    
    public $categoria_id = '';
    public $nombre = '';

    // Resetear paginación al buscar
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function rules()
    {
        return [
            'nombre' => [
                'required', 
                'string', 
                'max:100',
                Rule::unique('categoria_modulo', 'nombre')->ignore($this->categoria_id, 'id_categoria_modulo')
            ]
        ];
    }

    public function render()
    {
        $search = mb_strtolower(trim($this->search));
        
        $categorias = CategoriaModuloModel::whereRaw('LOWER(nombre) LIKE ?', ["%{$search}%"])
            ->orderBy('nombre', 'asc')
            ->paginate(10);

        return view('livewire.categorias-modulos', compact('categorias'));
    }

    public function openModal()
    {
        $this->limpiarDatos();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->limpiarDatos();
    }

    public function limpiarDatos()
    {
        $this->categoria_id = '';
        $this->nombre = '';
        $this->resetValidation();
    }

    public function guardarCategoria()
    {
        $this->validate();

        if ($this->categoria_id) {
            $categoria = CategoriaModuloModel::find($this->categoria_id);
            $categoria->nombre = $this->nombre;
            $categoria->save();
            $mensaje = 'Categoría actualizada exitosamente';
        } else {
            CategoriaModuloModel::create([
                'nombre' => $this->nombre,
            ]);
            $mensaje = 'Categoría creada exitosamente';
        }

        $this->closeModal();
        $this->dispatch('toast', ['icon' => 'success', 'title' => $mensaje]);
    }

    public function editar($id)
    {
        $categoria = CategoriaModuloModel::findOrFail($id);
        $this->categoria_id = $categoria->id_categoria_modulo;
        $this->nombre = $categoria->nombre;
        
        $this->showModal = true;
    }

    public function eliminar($id)
    {
        // 1. VERIFICACIÓN DE SEGURIDAD (¿Está en uso?)
        $enUso = ModuloModel::where('id_categoria_modulo', $id)->exists();

        if ($enUso) {
            // Si está en uso, lanzamos error y abortamos
            $this->dispatch('toast', [
                'icon' => 'error', 
                'title' => 'No se puede eliminar: Hay módulos usando esta categoría.'
            ]);
            return;
        }

        // 2. Si está libre, la eliminamos
        CategoriaModuloModel::destroy($id);
        
        $this->dispatch('toast', [
            'icon' => 'success', 
            'title' => 'Categoría eliminada correctamente'
        ]);
    }
}
