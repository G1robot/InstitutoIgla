<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CajaModel;
use App\Models\TurnoModel;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AperturaCaja extends Component
{
    public $necesitaApertura = false;
    public $turnos = [];
    
    public $id_turno = '';
    public $monto_inicial = 0;

    public function mount()
    {
        $this->verificarCaja();
    }

    public function verificarCaja()
    {
        if (Auth::check()) {

            if (Auth::user()->rol === 'administrador') {
                $this->necesitaApertura = false;
                return;
            }

            if (request()->routeIs('reporte-arqueo')) {
                $this->necesitaApertura = false;
                return;
            }
            
            $cajaAbierta = CajaModel::where('id_usuario', Auth::id())
                                    ->where('estado', 'abierta')
                                    ->first();

            if (!$cajaAbierta) {
                $this->necesitaApertura = true;
                $this->turnos = TurnoModel::all();
            } else {
                $this->necesitaApertura = false;
            }
        }
    }

    public function abrirCaja()
    {
        $this->validate([
            'id_turno' => 'required|exists:turno,id_turno',
            'monto_inicial' => 'required|numeric|min:0',
        ]);

        CajaModel::create([
            'id_usuario' => Auth::id(),
            'id_turno' => $this->id_turno,
            'fecha_apertura' => Carbon::now(),
            'monto_inicial' => $this->monto_inicial,
            'estado' => 'abierta'
        ]);

        $this->necesitaApertura = false;
        
        // Recargamos la página para que el resto del sistema sepa que ya hay caja
        return redirect(request()->header('Referer'));
    }

    public function render()
    {
        return view('livewire.apertura-caja');
    }
}
