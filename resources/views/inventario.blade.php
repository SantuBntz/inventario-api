<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventario en vivo</title>
    @vite(['resources/js/app.js'])
    <style>
        body { font-family: system-ui, sans-serif; margin: 2rem; }
        table { border-collapse: collapse; width: 100%; max-width: 760px; }
        th, td { border: 1px solid #999; padding: .5rem .75rem; text-align: left; }
        .flash { background: #fff3a0; }
    </style>
</head>
<body>
    <h1>Inventario en vivo</h1>
    <p>Conexión: <strong id="estado">conectando...</strong></p>

    <table>
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th><th>Cambiar stock</th></tr>
        </thead>
        <tbody id="filas"></tbody>
    </table>

    <script>
        const productos = @json($productos);
        const filas = document.getElementById('filas');

        function pintar(p) {
            let tr = document.getElementById('p-' + p.id);
            if (!tr) {
                tr = document.createElement('tr');
                tr.id = 'p-' + p.id;
                tr.innerHTML = `<td>${p.id}</td><td data-c="nombre"></td><td data-c="precio"></td><td data-c="stock"></td>
                    <td><input type="number" min="0" style="width:80px"> <button>Guardar</button></td>`;
                tr.querySelector('button').onclick = () => guardar(p.id, tr.querySelector('input').value);
                filas.appendChild(tr);
            }
            tr.querySelector('[data-c=nombre]').textContent = p.nombre;
            tr.querySelector('[data-c=precio]').textContent = p.precio;
            tr.querySelector('[data-c=stock]').textContent = p.stock;
            tr.querySelector('input').value = p.stock;
            tr.classList.add('flash');
            setTimeout(() => tr.classList.remove('flash'), 800);
        }

        async function guardar(id, stock) {
            const r = await fetch('/api/productos/' + id, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ stock: parseInt(stock, 10) })
            });
            if (!r.ok) alert('Error ' + r.status);
        }

        productos.forEach(pintar);

        // Echo ya está cargado cuando el módulo de Vite se ejecuta
        window.addEventListener('DOMContentLoaded', () => {
            window.Echo.connector.pusher.connection.bind('state_change', s => {
                document.getElementById('estado').textContent = s.current;
            });

            window.Echo.channel('inventario')
                .listen('.producto.actualizado', (evento) => {
                    console.log('Producto actualizado:', evento);
                    pintar(evento);
                });
        });
    </script>
</body>
</html>