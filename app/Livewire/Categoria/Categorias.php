<?php

namespace App\Livewire\Categoria;

use App\Models\Categorias\Categoria;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class Categorias extends Component
{
    public $categorias;
    public $nombre;
    public $descripcion;
    public $activo = true;
    public $categoria_id;
    public $isEdit = false;

    public function render()
    {
        $this->categorias = Categoria::all();
        return view('livewire.categoria.categorias');
    }

    public function store()
    {
        $this->validate([
            'nombre' => 'required'
        ]);

        Categoria::create([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'activo' => $this->activo,
        ]);

        $this->resetInput();

        Toaster::success('La categoría fue registrada correctamente');
    }

    public function edit($id)
    {
        $categoria = Categoria::findOrFail($id);
        $this->fill($categoria->toArray());
        $this->categoria_id = $id;
        $this->isEdit = true;
    }

    public function update()
    {
        $this->validate([
            'nombre' => 'required'
        ]);

        Categoria::findOrFail($this->categoria_id)->update([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'activo' => $this->activo,
        ]);

        $this->resetInput();

        Toaster::success('La categoría fue actualizada correctamente');
    }

    #[\Livewire\Attributes\On('eliminarCategoria')]
    public function delete($id)
    {
        Categoria::destroy($id);

        Toaster::success('La categoría fue eliminada correctamente');
    }

    private function resetInput()
    {
        $this->reset([
            'nombre',
            'descripcion',
            'activo',
            'categoria_id',
            'isEdit',
        ]);
    }
}
