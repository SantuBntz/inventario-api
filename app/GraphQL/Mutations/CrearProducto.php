<?php

namespace App\GraphQL\Mutations;

use App\Models\Producto;
use App\Services\ProductoService;
use Illuminate\Support\Facades\Validator;
use Nuwave\Lighthouse\Exceptions\ValidationException;

final class CrearProducto
{
    public function __construct(private ProductoService $productos)
    {
    }

    public function __invoke(mixed $root, array $args): Producto
    {
        // Mismas reglas que REST (ProductoService::REGLAS)
        $validador = Validator::make($args, ProductoService::REGLAS);

        if ($validador->fails()) {
            throw ValidationException::withMessages($validador->errors()->toArray());
        }

        // Mismo método del service que usa el controlador REST
        return $this->productos->crear($validador->validated());
    }
}