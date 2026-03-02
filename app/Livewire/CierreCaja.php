<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CajaModel;
use App\Models\TransaccionModel;
use App\Models\EgresoModel;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Livewire\Attributes\On;

class CierreCaja extends Component
{
    public $showModal = false;
    public $monto_fisico = '';
    public $cajaAbierta;

    // Escuchamos el evento desde el menú principal

    #[On('solicitarCierre')]
    public function abrirModal()
    {
        $this->cajaAbierta = CajaModel::where('id_usuario', Auth::id())
                                      ->where('estado', 'abierta')
                                      ->first();

        if (!$this->cajaAbierta) {
            // Si NO tiene caja abierta, lo deslogueamos directamente
            return $this->ejecutarLogout();
        }

        // Si SÍ tiene, mostramos el modal
        $this->monto_fisico = '';
        $this->showModal = true;
    }

    public function confirmarCierre()
    {
        $this->validate([
            'monto_fisico' => 'required|numeric|min:0'
        ]);

        // 1. Cálculos de Auditoría (Para guardar en BD)
        $ingresos = TransaccionModel::where('id_caja', $this->cajaAbierta->id_caja)->sum('monto');
        $egresos = EgresoModel::where('id_caja', $this->cajaAbierta->id_caja)->sum('monto');
        
        $monto_sistema = $this->cajaAbierta->monto_inicial + $ingresos - $egresos;
        $diferencia = $this->monto_fisico - $monto_sistema;

        // 2. Cerrar la caja
        $this->cajaAbierta->update([
            'fecha_cierre' => Carbon::now(),
            'monto_final_sistema' => $monto_sistema,
            'monto_final_fisico' => $this->monto_fisico,
            'diferencia' => $diferencia,
            'estado' => 'cerrada'
        ]);

        $this->showModal = false;

        // 3. Redirigir al Reporte de Arqueo para que imprima
        return redirect()->route('reporte-arqueo')->with('success', 'Turno cerrado correctamente. Por favor, imprime tu reporte del día.');
    }

    public function ejecutarLogout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        
        return redirect()->route('login'); // Asegúrate de que tu ruta de login se llame así
    }

    public function render()
    {
        return view('livewire.cierre-caja');
    }
}
