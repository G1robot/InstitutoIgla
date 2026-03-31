<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\UsuarioModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PerfilUsuario extends Component
{
    use WithFileUploads;

    public $usuario_id;
    public $nombre, $telefono, $usuario, $rol;
    public $contrasena, $contrasena1;
    
    public $foto; 
    public $fotoActual; 

    public function mount()
    {
        
        $user = Auth::user();
        $this->usuario_id = $user->id_usuario; 
        $this->nombre = $user->nombre;
        $this->telefono = $user->telefono;
        $this->usuario = $user->usuario;
        $this->rol = $user->rol;
        $this->fotoActual = $user->foto;
    }

    public function guardarPerfil()
    {
        // 1. Validaciones base
        $rules = [
            'nombre' => 'required',
            'telefono' => 'required',
            // ¡CORRECCIÓN AQUÍ! Cambiamos 'usuarios' por 'usuario'
            'usuario' => 'required|unique:usuario,usuario,' . $this->usuario_id . ',id_usuario', 
            'foto' => 'nullable|image|max:2048', 
        ];

        // 2. MAGIA DE LA CONTRASEÑA: Solo validamos si escribió algo
        if (!empty($this->contrasena)) {
            $rules['contrasena'] = 'min:6';
            $rules['contrasena1'] = 'same:contrasena';
        }

        $this->validate($rules);

        // 3. Buscar el usuario
        $user = UsuarioModel::find($this->usuario_id);

        // 4. Procesar la foto si subió una nueva
        if ($this->foto) {
            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }
            $path = $this->foto->store('perfiles', 'public');
            $user->foto = $path;
            $this->fotoActual = $path; 
        }

        // 5. Actualizar datos
        $user->nombre = $this->nombre;
        $user->telefono = $this->telefono;
        $user->usuario = $this->usuario;
        
        // ¡CORRECCIÓN AQUÍ! Usamos 'password' tal como está en tu fillable
        if (!empty($this->contrasena)) {
            $user->password = Hash::make($this->contrasena); 
        }

        $user->save();

        // 6. Limpiar campos de contraseña y avisar éxito
        $this->contrasena = '';
        $this->contrasena1 = '';

        $this->dispatch('toast', [
            'icon' => 'success', 
            'title' => 'Perfil actualizado correctamente'
        ]);
        
        $this->dispatch('perfilActualizado');
    }
    
    public function render()
    {
        return view('livewire.perfil-usuario');
    }
}
