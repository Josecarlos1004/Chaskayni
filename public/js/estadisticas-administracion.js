(() => {
    const panel = document.querySelector('#panel-estadisticas');
    const grafico = document.querySelector('#grafico-estadistico');
    const leyenda = document.querySelector('#leyenda-grafico-estadistico');
    const estado = document.querySelector('#estado-actualizacion-estadisticas');

    if (!panel || !grafico || !leyenda || !estado) return;

    const espacioSvg = 'http://www.w3.org/2000/svg';
    const series = [
        { clave: 'usuarios', nombre: 'Cuentas', color: '#176b50' },
        { clave: 'productos', nombre: 'Productos', color: '#d49b2f' },
        { clave: 'pedidos', nombre: 'Pedidos pendientes', color: '#3779a8' },
        { clave: 'reportes', nombre: 'Reportes pendientes', color: '#a84e43' },
    ];

    function crearElementoSvg(nombre, atributos = {}) {
        const elemento = document.createElementNS(espacioSvg, nombre);
        Object.entries(atributos).forEach(([clave, valor]) => elemento.setAttribute(clave, valor));
        return elemento;
    }

    function dibujarGrafico(datos) {
        const totalPorCategoria = series.map(({ clave }) => Number(datos.series[clave] || 0));
        const totalGeneral = totalPorCategoria.reduce((total, cantidad) => total + cantidad, 0);
        const radio = 92;
        const circunferencia = 2 * Math.PI * radio;
        let recorrido = 0;

        grafico.replaceChildren(grafico.querySelector('desc'));
        grafico.append(crearElementoSvg('circle', {
            cx: 200,
            cy: 140,
            r: radio,
            class: 'fondo-dona-estadistica',
        }));

        series.forEach(({ clave, nombre, color }, indice) => {
            const cantidad = totalPorCategoria[indice];
            if (cantidad === 0 || totalGeneral === 0) return;

            const longitud = (cantidad / totalGeneral) * circunferencia;
            const segmento = crearElementoSvg('circle', {
                cx: 200,
                cy: 140,
                r: radio,
                class: 'segmento-dona-estadistica',
                stroke: color,
                'stroke-dasharray': `${longitud} ${circunferencia - longitud}`,
                'stroke-dashoffset': -recorrido,
                transform: 'rotate(-90 200 140)',
            });
            const descripcion = crearElementoSvg('title');
            descripcion.textContent = `${nombre}: ${cantidad} registros`;
            segmento.append(descripcion);
            grafico.append(segmento);
            recorrido += longitud;
        });

        const cifraCentral = crearElementoSvg('text', {
            x: 200,
            y: 137,
            class: 'centro-dona-estadistica centro-dona-total',
        });
        cifraCentral.textContent = totalGeneral;
        const etiquetaCentral = crearElementoSvg('text', {
            x: 200,
            y: 161,
            class: 'centro-dona-estadistica centro-dona-etiqueta',
        });
        etiquetaCentral.textContent = 'registros';
        grafico.append(cifraCentral, etiquetaCentral);

        leyenda.replaceChildren();
        series.forEach(({ nombre, color }, indice) => {
            const cantidad = totalPorCategoria[indice];
            const porcentaje = totalGeneral ? (cantidad / totalGeneral) * 100 : 0;
            const fila = document.createElement('li');
            const nombreCategoria = document.createElement('span');
            const punto = document.createElement('span');
            const textoNombre = document.createElement('span');
            const valor = document.createElement('strong');

            nombreCategoria.className = 'detalle-leyenda-estadistica';
            punto.className = 'indicador-leyenda';
            punto.style.backgroundColor = color;
            textoNombre.textContent = nombre;
            valor.className = 'valor-leyenda-estadistica';
            valor.textContent = `${cantidad} · ${new Intl.NumberFormat('es-PE', { maximumFractionDigits: 1 }).format(porcentaje)}%`;
            nombreCategoria.append(punto, textoNombre);
            fila.append(nombreCategoria, valor);
            leyenda.append(fila);
        });
    }

    async function actualizarEstadisticas() {
        estado.setAttribute('aria-busy', 'true');
        estado.textContent = 'Actualizando datos…';
        try {
            const respuesta = await fetch(panel.dataset.url, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            if (!respuesta.ok) throw new Error('No se pudo consultar el resumen.');

            const datos = await respuesta.json();
            Object.entries(datos.indicadores).forEach(([clave, valor]) => {
                const indicador = panel.querySelector(`[data-indicador="${clave}"]`);
                if (indicador) indicador.textContent = valor;
            });

            dibujarGrafico(datos.grafico);
            estado.textContent = `Actualizado a las ${datos.actualizado_en} · se renueva cada minuto`;
        } catch {
            estado.textContent = 'No se pudo actualizar ahora; se volverá a intentar en un minuto.';
        } finally {
            estado.setAttribute('aria-busy', 'false');
            window.setTimeout(actualizarEstadisticas, 60_000);
        }
    }

    actualizarEstadisticas();
})();