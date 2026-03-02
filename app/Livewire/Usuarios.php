<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\UsuarioModel;
use Illuminate\Support\Facades\Hash;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class Usuarios extends Component
{
    use WithPagination;
    public $search = '';
    public $showModal = false;
    
    public $nombre = '';
    public $telefono = '';
    public $usuario = '';
    public $contrasena = '';
    public $contrasena1 = '';
    public $rol = '';
    public $usuario_id ='';

    public function rules(){
        $rules = [
            'nombre' => 'required|string|regex:/^[A-Za-z\s]+$/|max:255',
            'telefono' => 'required|string|regex:/^\d+$/|max:255',
            'usuario' => [
                'required',
                'string',
                'max:255',
                Rule::unique('usuario', 'usuario')->ignore($this->usuario_id, 'id_usuario') 
            ],
            'contrasena' => 'required|string|min:6',
            'contrasena1' => 'required|string|same:contrasena',
            'rol' => 'required|in:administrador,personal',
        ];
        return $rules;
    }

    public function render()
    {
        $search = mb_strtolower(trim($this->search));
        $usuarios = UsuarioModel::whereRaw('LOWER(nombre) LIKE ?', ["%{$search}%"])
            ->orWhereRaw('LOWER(usuario) LIKE ?', ["%{$search}%"])
            ->orderBy('nombre', 'asc')
            ->orderBy('id_usuario', 'asc')
            ->paginate(10);
        return view('livewire.usuarios', compact('usuarios'));
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
        $this->telefono='';
        $this->usuario='';
        $this->contrasena='';
        $this->contrasena1='';
        $this->rol='';
        $this->usuario_id='';
    }

    public function enviarClick()
    {
        $this->validate();
        if ($this->usuario_id) {
            $usuario = UsuarioModel::find($this->usuario_id);
            $usuario->nombre = $this->nombre;
            $usuario->telefono = $this->telefono;
            if (!empty($this->contrasena)) {
                $usuario->password = Hash::make($this->contrasena); // ENCRIPTADO
            }
            $usuario->usuario = $this->usuario;
            $usuario->rol = $this->rol;
            $usuario->save();
            $this->usuario_id='';
        } else {
            UsuarioModel::create([
                'nombre' => $this->nombre,
                'telefono' => $this->telefono,
                'usuario' => $this->usuario,
                'password' => Hash::make($this->contrasena),
                'rol' => $this->rol,
            ]);
        }
        $this->limpiarDatos();
        $this->closeModal();

        $this->dispatch('toast', [
            'icon' => 'success', 
            'title' => 'Usuario guardado exitosamente'
        ]);
    }

    public function editar($id){
        $usuario = UsuarioModel::findOrFail($id);
        $this->nombre = $usuario->nombre;
        $this->telefono = $usuario->telefono;
        $this->usuario = $usuario->usuario;
        $this->contrasena = $usuario->contrasena;
        $this->contrasena1 = $usuario->contrasena;
        $this->rol = $usuario->rol;

        $this->usuario_id = $id;
        $this->openModal();
    }    
}
