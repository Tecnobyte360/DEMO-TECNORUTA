<?php

namespace App\Livewire\SubCategorias;

use App\Models\Categorias\Subcategoria;
use App\Models\Categorias\Categoria;
use Livewire\Component;
use Masmerise\Toaster\PendingToast;
use Illuminate\Support\Facades\Log;

class SubCategorias extends Component
{
    public $subcategorias, $categoria_id, $nombre, $descripcion, $activo = true, $subcategoria_id;
    public $isEdit = false;

    public function render()
    {
        $this->subcategorias = Subcategoria::with('categoria')->get();
        return view('livewire.sub-categorias.sub-categorias', [
            'categorias' => Categoria::all()
        ]);
    }

    public function store()
    {
        try {
            $this->validate([
                'categoria_id' => 'required',
                'nombre' => 'required'
            ]);

            Subcategoria::create($this->only(['categoria_id', 'nombre', 'descripcion', 'activo']));

            $this->resetInput();

            PendingToast::create()
                ->success()
                ->message('Subcategoría registrada correctamente.')
                ->duration(5000);
        } catch (\Throwable $e) {
            Log::error('Error al registrar subcategoría', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            PendingToast::create()
                ->error()
                ->message('Error al registrar la subcategoría: ' . $e->getMessage())
                ->duration(9000);
        }
    }
public function edit($id)
{
    try {
        $sub = Subcategoria::findOrFail($id);
        $this->fill($sub->toArray());
        $this->activo = (bool) $sub->activo; 
        $this->subcategoria_id = $id;
        $this->isEdit = true;
    } catch (\Throwable $e) {
        Log::error('Error al cargar subcategoría para edición', [
            'id' => $id,
            'message' => $e->getMessage(),
        ]);

        PendingToast::create()
            ->error()
            ->message('Error al cargar la subcategoría.')
            ->duration(7000);
    }
}

    public function update()
    {
        try {
            $this->validate([
                'categoria_id' => 'required',
                'nombre' => 'required'
            ]);

            Subcategoria::findOrFail($this->subcategoria_id)
                ->update($this->only(['categoria_id', 'nombre', 'descripcion', 'activo']));

            $this->resetInput();

            PendingToast::create()
                ->success()
                ->message('Subcategoría actualizada correctamente.')
                ->duration(5000);
        } catch (\Throwable $e) {
            Log::error('Error al actualizar subcategoría', [
                'id' => $this->subcategoria_id,
                'message' => $e->getMessage(),
            ]);

            PendingToast::create()
                ->error()
                ->message('Error al actualizar la subcategoría: ' . $e->getMessage())
                ->duration(9000);
        }
    }

    public function delete($id)
    {
        try {
            Subcategoria::destroy($id);

            PendingToast::create()
                ->success()
                ->message('Subcategoría eliminada correctamente.')
                ->duration(5000);
        } catch (\Throwable $e) {
            Log::error('Error al eliminar subcategoría', [
                'id' => $id,
                'message' => $e->getMessage(),
            ]);

            PendingToast::create()
                ->error()
                ->message('Error al eliminar la subcategoría.')
                ->duration(9000);
        }
    }

    private function resetInput()
    {
        $this->reset(['categoria_id', 'nombre', 'descripcion', 'activo', 'subcategoria_id', 'isEdit']);
    }
}
