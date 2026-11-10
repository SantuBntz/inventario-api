const path = require('path');
const grpc = require('@grpc/grpc-js');
const protoLoader = require('@grpc/proto-loader');

const LARAVEL_URL = process.env.LARAVEL_URL || 'http://127.0.0.1:8000';
const TOKEN = process.env.INTERNAL_API_TOKEN || 'clave-interna-123';

const definicion = protoLoader.loadSync(path.join(__dirname, 'producto.proto'), {
  keepCase: true, longs: String, enums: String, defaults: true, oneofs: true,
});
const proto = grpc.loadPackageDefinition(definicion).inventario;

// Implementación del método ObtenerProducto del contrato
async function obtenerProducto(call, callback) {
  const id = call.request.id;
  try {
    const res = await fetch(`${LARAVEL_URL}/api/interno/productos/${id}`, {
      headers: { Accept: 'application/json', 'X-Internal-Token': TOKEN },
    });

    if (res.status === 404) {
      return callback({ code: grpc.status.NOT_FOUND, message: `El producto ${id} no existe` });
    }
    if (!res.ok) {
      return callback({ code: grpc.status.INTERNAL, message: `Laravel respondió ${res.status}` });
    }

    const p = await res.json();
    // Laravel devuelve el precio como texto ("18500.00"): se convierte a número
    callback(null, { id: p.id, nombre: p.nombre, precio: Number(p.precio), stock: p.stock });
    } catch (e) {
    console.error('Error al llamar a Laravel:', e);
    callback({ code: grpc.status.UNAVAILABLE, message: 'No se pudo contactar a Laravel' });
  }
}
    


const server = new grpc.Server();
server.addService(proto.ProductoService.service, { ObtenerProducto: obtenerProducto });

server.bindAsync('0.0.0.0:50051', grpc.ServerCredentials.createInsecure(), (err, puerto) => {
  if (err) throw err;
  console.log(`Servidor gRPC escuchando en el puerto ${puerto}`);
});