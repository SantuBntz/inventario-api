<?php

namespace App\Events;

use App\Models\Producto;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductoActualizado implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    // Las propiedades públicas son lo que recibe el navegador
    public int $id;
    public string $nombre;
    public float $precio;
    public int $stock;

    public function __construct(Producto $producto)
    {
        $this->id     = $producto->id;
        $this->nombre = $producto->nombre;
        $this->precio = (float) $producto->precio;
        $this->stock  = $producto->stock;
    }

    public function broadcastOn(): array
    {
        return [new Channel('inventario')];
    }

    public function broadcastAs(): string
    {
        return 'producto.actualizado';
    }
}