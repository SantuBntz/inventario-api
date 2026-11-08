<?php

namespace App\Services;
use App\Events\ProductoActualizado;
use App\Models\Producto;

class ProductoService
{
    // Validaciones compartidas por REST, GraphQL y gRPC
    public const REGLAS = [
        'nombre' => 'required|string|max:100',
        'precio' => 'required|numeric|min:0',
        'stock'  => 'required|integer|min:0',
    ];

   public function crear(array $datos): Producto
{
    $producto = Producto::create($datos);
    ProductoActualizado::dispatch($producto);

    return $producto;
}

    public function actualizar(Producto $producto, array $datos): Producto
{
    $producto->update($datos);
    ProductoActualizado::dispatch($producto);

    return $producto;
}

    public function eliminar(Producto $producto): void
    {
        $producto->delete();
    }
}