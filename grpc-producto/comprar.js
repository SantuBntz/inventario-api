const path = require('path');
const grpc = require('@grpc/grpc-js');
const protoLoader = require('@grpc/proto-loader');

const LARAVEL = process.env.LARAVEL_URL || 'http://127.0.0.1:8000';
const ID = parseInt(process.argv[2] || '1', 10);
const N = 10;

const def = protoLoader.loadSync(path.join(__dirname, 'producto.proto'), {
  keepCase: true, longs: String, enums: String, defaults: true, oneofs: true,
});
const proto = grpc.loadPackageDefinition(def).inventario;
const cliente = new proto.ProductoService('localhost:50051', grpc.credentials.createInsecure());

const ms = (t0) => Number(process.hrtime.bigint() - t0) / 1e6;
const mediana = (a) => {
  const s = [...a].sort((x, y) => x - y);
  const m = Math.floor(s.length / 2);
  return s.length % 2 ? s[m] : (s[m - 1] + s[m]) / 2;
};

async function graphql(query) {
  const t0 = process.hrtime.bigint();
  const r = await fetch(`${LARAVEL}/graphql`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ query }),
  });
  const txt = await r.text();
  return { t: ms(t0), b: Buffer.byteLength(txt) };
}

const pruebas = {
  'REST': async () => {
    const t0 = process.hrtime.bigint();
    const r = await fetch(`${LARAVEL}/api/productos/${ID}`, { headers: { Accept: 'application/json' } });
    const txt = await r.text();
    return { t: ms(t0), b: Buffer.byteLength(txt) };
  },
  'GraphQL (4 campos)': () => graphql(`{ producto(id: ${ID}) { id nombre precio stock } }`),
  'GraphQL (2 campos)': () => graphql(`{ producto(id: ${ID}) { nombre precio } }`),
  'gRPC': () => new Promise((resolve, reject) => {
    const t0 = process.hrtime.bigint();
    cliente.ObtenerProducto({ id: ID }, (err, resp) => {
      const t = ms(t0);
      if (err) return reject(err);
      resolve({ t, b: proto.ProductoService.service.ObtenerProducto.responseSerialize(resp).length });
    });
  }),
};

(async () => {
  console.log(`Producto ${ID}: mediana de ${N} llamadas (sin contar la primera)\n`);
  for (const [nombre, fn] of Object.entries(pruebas)) {
    try {
      await fn(); // llamada de calentamiento
      const tiempos = [];
      let bytes = 0;
      for (let i = 0; i < N; i++) {
        const r = await fn();
        tiempos.push(r.t);
        bytes = r.b;
      }
      console.log(`${nombre.padEnd(20)} ${mediana(tiempos).toFixed(1).padStart(7)} ms   ${String(bytes).padStart(4)} B`);
    } catch (e) {
      console.log(`${nombre.padEnd(20)} ERROR: ${e.details || e.message}`);
    }
  }
})();