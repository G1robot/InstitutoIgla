<?php

namespace App\Livewire;

use App\Models\EstudianteModel;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class Estudiantes extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    
    public $nombre = '';
    public $apellido = '';
    public $ci = '';
    public $telefono = '';
    public $fecha_nacimiento = '';
    public $genero = '';
    public $estudiante_id = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function rules(){
        $rules = [
            'nombre' => 'required|string|regex:/^[A-Za-z\s]+$/|max:255',
            'apellido' => 'required|string|regex:/^[A-Za-z\s]+$/|max:255',
            'ci' => [
                'required',
                'string',
                'max:255',
                'regex:/^\d{6,8}(-[A-Za-z0-9]{1,2})?$/',
                // Asegúrate que 'estudiante' sea el nombre exacto de tu tabla en BD
                Rule::unique('estudiante', 'ci')->ignore($this->estudiante_id, 'id_estudiante') 
            ],
            'telefono' => 'required|string|regex:/^\d+$/|max:255',
            'fecha_nacimiento' => 'required|date',
            'genero' => 'required|in:masculino,femenino',
        ];
        return $rules;
    }

    public function render()
    {
        $search = mb_strtolower(trim($this->search));
        $estudiantes = EstudianteModel::whereRaw('LOWER(nombre) LIKE ?', ["%{$search}%"])
            ->orWhereRaw('LOWER(apellido) LIKE ?', ["%{$search}%"])
            ->orWhere('ci', 'like', '%' . $this->search . '%')
            ->orderBy('apellido', 'asc')
            ->orderBy('id_estudiante', 'asc')
            ->paginate(10);
        return view('livewire.estudiantes', compact('estudiantes'));
    }
    public function clickBuscar(){

    }

    public function openModal()
    {
        $this->showModal = true;
    }
    public function closeModal()
    {
        $this->showModal = false;
       $this->limpiarDatos();
    }
    public function limpiarDatos(){
        $this->nombre='';
        $this->apellido='';
        $this->telefono='';
        $this->ci='';
        $this->fecha_nacimiento='';
        $this->genero='';
        $this->estudiante_id='';
    }
    public function enviarClick()
    {
        $this->validate();

        if ($this->estudiante_id) {
            $estudiante = EstudianteModel::find($this->estudiante_id);
            $estudiante->nombre = $this->nombre;
            $estudiante->apellido = $this->apellido;
            $estudiante->ci = $this->ci;
            $estudiante->telefono = $this->telefono;
            $estudiante->fecha_nacimiento = $this->fecha_nacimiento;
            $estudiante->genero = $this->genero;
            $estudiante->save();
        } else {
            EstudianteModel::create([
                'nombre' => $this->nombre,
                'apellido' => $this->apellido,
                'ci' => $this->ci,
                'telefono' => $this->telefono,
                'fecha_nacimiento' => $this->fecha_nacimiento,
                'genero' => $this->genero,
            ]);
        }
        $this->limpiarDatos();
        $this->closeModal();

        $this->dispatch('toast', [
            'icon' => 'success', 
            'title' => 'Estudiante guardado exitosamente'
        ]);
    }
    public function editar($id){
        $estudiante = EstudianteModel::findOrFail($id);
        $this->nombre = $estudiante->nombre;
        $this->apellido = $estudiante->apellido;
        $this->ci = $estudiante->ci;
        $this->telefono = $estudiante->telefono;
        $this->fecha_nacimiento = $estudiante->fecha_nacimiento;
        $this->genero = $estudiante->genero;
        $this->estudiante_id = $id;
        $this->openModal();
    }

}
