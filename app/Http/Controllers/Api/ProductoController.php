<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Services\ProductoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function __construct(private ProductoService $productos)
    {
    }

    // GET /api/productos -> 200
    public function index(): JsonResponse
    {
        return response()->json(Producto::all());
    }

    // POST /api/productos -> 201 (o 422 si no valida)
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate(ProductoService::REGLAS);

        return response()->json($this->productos->crear($datos), 201);
    }

    // GET /api/productos/{id} -> 200 (o 404 si no existe)
    public function show(Producto $producto): JsonResponse
    {
        return response()->json($producto);
    }

    // PUT o PATCH /api/productos/{id} -> 200 (404 si no existe, 422 si no valida)
    public function update(Request $request, Producto $producto): JsonResponse
    {
        // En PATCH se permite mandar solo algunos campos
        $reglas = $request->isMethod('patch')
            ? array_map(fn ($regla) => 'sometimes|' . $regla, ProductoService::REGLAS)
            : ProductoService::REGLAS;

        $datos = $request->validate($reglas);

        return response()->json($this->productos->actualizar($producto, $datos));
    }

    // DELETE /api/productos/{id} -> 204 sin contenido (o 404 si no existe)
    public function destroy(Producto $producto): JsonResponse
    {
        $this->productos->eliminar($producto);

        return response()->json(null, 204);
    }
}