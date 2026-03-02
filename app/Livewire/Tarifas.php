<?php

namespace App\Livewire;

use App\Models\TarifaModel;
use Livewire\Component;

class Tarifas extends Component
{
    public $search = '';
    public $showModal = false;

    // Campos de la BD
    public $tarifa_id = '';
    public $codigo = ''; // Ej: PUA, PUP
    public $monto = '';
    public $gestion = ''; // Puede ser null

    public function rules(){
        return [
            'codigo'  => 'required|string|max:50', // Sin regex estricto para permitir números si quieren (ej: PLAN2024)
            'monto'   => 'required|numeric|min:0',
            'gestion' => 'nullable|integer|digits:4', // Ej: 2024
        ];
    }

    public function render()
    {
        $search = mb_strtolower(trim($this->search));
        
        $tarifas = TarifaModel::whereRaw('LOWER(codigo) LIKE ?', ["%{$search}%"])
            ->orWhere('gestion', 'like', '%' . $this->search . '%')
            ->orderBy('gestion', 'desc') 
            ->orderBy('id_tarifa', 'desc')
            ->get();
        return view('livewire.tarifas', compact('tarifas'));
    }

    public function clickBuscar(){
        $this->render();
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

    public function limpiarDatos(){
        $this->tarifa_id = '';
        $this->codigo = '';
        $this->monto = '';
        $this->gestion = date('Y'); // Por defecto el año actual
    }

    public function enviarClick()
    {
        $this->validate();

        $query = TarifaModel::where('codigo', $this->codigo)
                             ->where('gestion', $this->gestion ?: null); // Asegura que busque null si está vacío

        if ($this->tarifa_id) {
            $query->where('id_tarifa', '!=', $this->tarifa_id);
        }

        // 3. Verificamos si existe
        if($query->exists()){
            $this->addError('codigo', 'Ya existe una tarifa con este código para esta gestión.');
            return;
        }

        if ($this->tarifa_id) {
            $tarifa = TarifaModel::find($this->tarifa_id);
            $tarifa->update([
                'codigo' => strtoupper($this->codigo),
                'monto' => $this->monto,
                'gestion' => $this->gestion ?: null,
            ]);
        } else {
            TarifaModel::create([
                'codigo' => strtoupper($this->codigo),
                'monto' => $this->monto,
                'gestion' => $this->gestion ?: null,
            ]);
        }
        $this->limpiarDatos();
        $this->closeModal();

        $this->dispatch('toast', [
            'icon' => 'success', 
            'title' => 'Tarifa guardada exitosamente'
        ]);
    }

    public function editar($id){
        $tarifa = TarifaModel::findOrFail($id);
        $this->tarifa_id = $id;
        $this->codigo = $tarifa->codigo;
        $this->monto = $tarifa->monto;
        $this->gestion = $tarifa->gestion;
        
        $this->showModal = true;
    }
}
