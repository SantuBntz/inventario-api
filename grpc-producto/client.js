const path = require('path');
const grpc = require('@grpc/grpc-js');
const protoLoader = require('@grpc/proto-loader');

const LARAVEL_URL = process.env.LARAVEL_URL || 'http://127.0.0.1:8000';
const ID = parseInt(process.argv[2] || '1', 10);
const VECES = 5;

const definicion = protoLoader.loadSync(path.join(__dirname, 'producto.proto'), {
  keepCase: true, longs: String, enums: String, defaults: true, oneofs: true,
});
const proto = grpc.loadPackageDefinition(definicion).inventario;
const cliente = new proto.ProductoService('localhost:50051', grpc.credentials.createInsecure());

const ms = (t0) => Number(process.hrtime.bigint() - t0) / 1e6;

function llamarGrpc(id) {
  return new Promise((resolve, reject) => {
    const t0 = process.hrtime.bigint();
    cliente.ObtenerProducto({ id }, (err, resp) => {
      const tiempo = ms(t0);
      if (err) return reject(err);
      // Tamaño real del mensaje binario (Protocol Buffers)
      const bytes = proto.ProductoService.service.ObtenerProducto.responseSerialize(resp).length;
      resolve({ resp, tiempo, bytes });
    });
  });
}

async function llamarRest(id) {
  const t0 = process.hrtime.bigint();
  const res = await fetch(`${LARAVEL_URL}/api/productos/${id}`, { headers: { Accept: 'application/json' } });
  const texto = await res.text();
  return { tiempo: ms(t0), bytes: Buffer.byteLength(texto) };
}

(async () => {
  console.log(`Producto con ID ${ID}\n`);
  for (let i = 1; i <= VECES; i++) {
    try {
      const g = await llamarGrpc(ID);
      const r = await llamarRest(ID);
      if (i === 1) console.log('Respuesta gRPC:', g.resp, '\n');
      console.log(`#${i}  gRPC: ${g.tiempo.toFixed(1)} ms, ${g.bytes} B   |   REST: ${r.tiempo.toFixed(1)} ms, ${r.bytes} B`);
    } catch (e) {
      console.error('Error:', e.details || e.message);
      process.exit(1);
    }
  }
})();