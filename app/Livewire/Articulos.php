<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ArticuloModel;
use App\Models\CategoriaArticuloModel;

class Articulos extends Component
{
    use WithPagination;

    public $search = '';
    
    // Control de Modales
    public $showModal = false;          // Modal Principal (Artículo)
    public $showCategoriaModal = false; // Modal Secundario (Categoría)

    // Datos del Artículo
    public $id_articulo;
    public $id_categoria_articulo;
    public $nombre;
    public $precio;
    public $stock; // Puede ser null (para servicios)
    public $es_obligatorio = false;
    public $es_servicio = false; // Checkbox visual para anular el stock

    // Datos para Nueva Categoría (Quick Add)
    public $nueva_categoria_nombre = '';

    public function rules() {
        return [
            'id_categoria_articulo' => 'required|exists:categorias_articulo,id_categoria_articulo',
            'nombre' => 'required|string|max:100',
            'precio' => 'required|numeric|min:0',
            // Si es servicio, stock puede ser null. Si no, debe ser entero.
            'stock' => $this->es_servicio ? 'nullable' : 'required|integer|min:0',
        ];
    }

    public function render()
    {
        $search = mb_strtolower(trim($this->search));

        $articulos = ArticuloModel::with('categoria')
            ->whereRaw('LOWER(nombre) LIKE ?', ["%{$search}%"])
            ->orWhereHas('categoria', function($q) use ($search) {
                $q->whereRaw('LOWER(nombre) LIKE ?', ["%{$search}%"]);
            })
            ->orderBy('id_articulo', 'desc')
            ->paginate(10);

        $categorias = CategoriaArticuloModel::orderBy('nombre', 'asc')->get();

        return view('livewire.articulos', compact('articulos', 'categorias'));
    }

    public function openModal() {
        $this->resetInput();
        $this->showModal = true;
    }

    public function closeModal() {
        $this->showModal = false;
        $this->resetInput();
    }

    public function resetInput() {
        $this->id_articulo = null;
        $this->id_categoria_articulo = '';
        $this->nombre = '';
        $this->precio = '';
        $this->stock = '';
        $this->es_obligatorio = false;
        $this->es_servicio = false;
        $this->resetValidation();
    }

    public function updatedEsServicio($value) {
        if($value) {
            $this->stock = null; // Si es servicio, borramos stock
        }
    }

    public function guardar() {
        $this->validate();

        $data = [
            'id_categoria_articulo' => $this->id_categoria_articulo,
            'nombre' => $this->nombre,
            'precio' => $this->precio,
            'stock' => $this->es_servicio ? null : $this->stock,
            'es_obligatorio' => $this->es_obligatorio ? true : false,
        ];

        if ($this->id_articulo) {
            ArticuloModel::find($this->id_articulo)->update($data);
            session()->flash('success', 'Artículo actualizado correctamente.');
        } else {
            ArticuloModel::create($data);
            session()->flash('success', 'Artículo creado correctamente.');
        }

        $this->closeModal();

        $this->dispatch('toast', [
            'icon' => 'success',
            'title' => 'Artículo guardado exitosamente',
        ]);
    }

    public function editar($id) {
        $art = ArticuloModel::find($id);
        $this->id_articulo = $id;
        $this->id_categoria_articulo = $art->id_categoria_articulo;
        $this->nombre = $art->nombre;
        $this->precio = $art->precio;
        $this->stock = $art->stock;
        $this->es_obligatorio = $art->es_obligatorio;
        
        // Si stock es null, asumimos que es un servicio
        $this->es_servicio = is_null($art->stock);

        $this->showModal = true;
    }
    
    public function eliminar($id) {
        // Opcional: Validar si tiene ventas antes de borrar
        ArticuloModel::destroy($id);
    }

    // --- LÓGICA DE CATEGORÍA RÁPIDA ---

    public function openCategoriaModal() {
        // No cerramos el modal principal, solo abrimos el segundo encima
        $this->nueva_categoria_nombre = '';
        $this->showCategoriaModal = true;
    }

    public function closeCategoriaModal() {
        $this->showCategoriaModal = false;
    }

    public function guardarCategoria() {
        $this->validate(['nueva_categoria_nombre' => 'required|string|max:50|unique:categorias_articulo,nombre']);

        $cat = CategoriaArticuloModel::create(['nombre' => $this->nueva_categoria_nombre]);

        // Seleccionamos la nueva categoría automáticamente en el form principal
        $this->id_categoria_articulo = $cat->id_categoria_articulo;
        
        $this->closeCategoriaModal();
        // Livewire refrescará automáticamente el <select> de categorías al renderizar
    }
}
