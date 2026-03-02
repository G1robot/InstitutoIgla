<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EgresoModel;
use App\Models\ProveedorModel;
use App\Models\MetodoPagoModel;
use Carbon\Carbon;
use App\Models\CajaModel;
use Illuminate\Support\Facades\Auth;

class Egresos extends Component
{
    use WithPagination;

    public $formKey = 1;
    public $datosRecibo = null;
    public $showModalExito = false;
    
    public $concepto;
    public $descripcion;
    public $monto;
    public $fecha_egreso;
    public $id_proveedor;
    public $id_metodo_pago;
    public $nro_factura;
    public $tipo_comprobante = 'recibo';

    
    public $metodosPago = [];
    public $proveedores = [];

    
    public $search = '';
    public $mesFilter;

    
    public $showModalProveedor = false;
    public $nuevo_proveedor_nombre = '';
    public $nuevo_proveedor_nit = '';

    public function mount()
    {
        $this->fecha_egreso = Carbon::now()->format('Y-m-d\TH:i'); 
        $this->metodosPago = MetodoPagoModel::where('activo', true)->get();
        $this->cargarProveedores();
        $this->mesFilter = Carbon::now()->format('Y-m'); 
    }

    public function cargarProveedores()
    {
        $this->proveedores = ProveedorModel::orderBy('nombre_empresa')->get();
    }

    public function render()
    {
        $egresos = EgresoModel::with(['proveedor', 'metodoPago'])
            ->whereRaw("LOWER(concepto) like ?", ['%' . strtolower($this->search) . '%'])
            ->where('fecha_egreso', 'like', $this->mesFilter . '%')
            ->orderBy('fecha_egreso', 'desc')
            ->paginate(10);
        return view('livewire.egresos', compact('egresos'));
    }

    public function limpiarDatos()
    {
        $this->concepto = '';
        $this->descripcion = '';
        $this->monto = '';
        $this->id_proveedor = '';
        $this->id_metodo_pago = '';
        $this->nro_factura = '';
        $this->tipo_comprobante = 'recibo';
        $this->fecha_egreso = Carbon::now('America/La_Paz')->format('Y-m-d\TH:i');
        $this->resetValidation();

        $this->formKey++;
    }
    
    public function guardarEgreso()
    {
        $this->validate([
            'concepto' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0.1',
            'fecha_egreso' => 'required',
            'id_metodo_pago' => 'required|exists:metodos_pago,id_metodo_pago',
            'tipo_comprobante' => 'required',
            
            'id_proveedor' => 'nullable|exists:proveedores,id_proveedor',
        ]);

        $cajaAbierta = CajaModel::where('id_usuario', Auth::id())
                            ->where('estado', 'abierta')
                            ->first();

        $egreso = EgresoModel::create([
            'id_caja' => $cajaAbierta->id_caja,
            'concepto' => $this->concepto,
            'descripcion' => $this->descripcion,
            'monto' => $this->monto,
            'fecha_egreso' => $this->fecha_egreso,
            'id_proveedor' => $this->id_proveedor ?: null, 
            'id_metodo_pago' => $this->id_metodo_pago,
            'nro_factura' => $this->nro_factura,
            'tipo_comprobante' => $this->tipo_comprobante,
        ]);

        $egreso->load(['proveedor', 'metodoPago']);

        $this->datosRecibo = [
            'nro_recibo' => str_pad($egreso->id_egreso, 6, '0', STR_PAD_LEFT),
            'fecha' => Carbon::parse($this->fecha_egreso)->format('d/m/Y H:i'),
            'cajero' => Auth::user()->nombre ?? 'Administrador',
            'proveedor' => $egreso->proveedor ? $egreso->proveedor->nombre_empresa : 'Varios / S/P',
            'concepto' => $this->concepto,
            'descripcion' => $this->descripcion,
            'monto' => $this->monto,
            'metodo_pago' => $egreso->metodoPago->nombre,
        ];

        
        $this->limpiarDatos();
        $this->showModalExito = true;
        
    }

    public function cerrarModalExito()
    {
        $this->showModalExito = false;
        $this->datosRecibo = null;
    }

    public function eliminar($id)
    {
        EgresoModel::destroy($id);
    }


    public function openModalProveedor() {
        $this->reset(['nuevo_proveedor_nombre', 'nuevo_proveedor_nit']);
        $this->showModalProveedor = true;
    }

    public function guardarProveedor() {
        $this->validate([
            'nuevo_proveedor_nombre' => 'required|min:3',
        ]);

        $prov = ProveedorModel::create([
            'nombre_empresa' => $this->nuevo_proveedor_nombre,
            'nit_ci' => $this->nuevo_proveedor_nit
        ]);

        $this->cargarProveedores();
        $this->id_proveedor = $prov->id_proveedor; 
        $this->showModalProveedor = false;
        
        session()->flash('success', 'Proveedor creado.');
    }
}
