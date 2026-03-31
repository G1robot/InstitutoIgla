<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Model;

class UsuarioModel extends Authenticatable
{
    use Notifiable;
    
    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';

    
    protected $fillable = [
        'nombre', 
        'telefono', 
        'usuario', 
        'password',
        'foto',
        'rol'
    ];

    // Esto oculta la contraseña cuando conviertes el usuario a JSON
    protected $hidden = [
        'password', // o 'contrasena'
        'remember_token',
    ];

    // ESTE MÉTODO ES LA CLAVE MÁGICA
    // Si tu columna se llama 'contrasena' en DB, pon 'contrasena' aquí. 
    // Si le hiciste caso a mi consejo anterior y la llamaste 'password', pon 'password'.
    public function getAuthPassword()
    {
        return $this->password; // o return $this->contrasena;
    }
}
