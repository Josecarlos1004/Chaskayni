/* Inicia el mapa comunitario y los selectores interactivos de coordenadas. */
(() => {
    const datos = window.datosMapa || {};
    const refugios = Array.isArray(datos.refugios) ? datos.refugios : [];
    const reportes = Array.isArray(datos.reportes) ? datos.reportes : [];
    const centroCusco = [-13.532, -71.967];

    const iniciarMapa = (elemento) => {
        if (!elemento || typeof L === 'undefined') return null;
        const ubicaciones = [...refugios, ...reportes].filter(
            (p) => Number.isFinite(Number(p.latitud)) && Number.isFinite(Number(p.longitud)),
        );
        const centro = ubicaciones.length ? [Number(ubicaciones[0].latitud), Number(ubicaciones[0].longitud)] : centroCusco;
        const mapa = L.map(elemento).setView(centro, ubicaciones.length ? 12 : 11);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap',
        }).addTo(mapa);
        return mapa;
    };

    const mapaPublico = iniciarMapa(document.getElementById('mapa-comunitario'));
    if (mapaPublico) {
        const limites = [];
        refugios.forEach((refugio) => {
            if (!Number.isFinite(Number(refugio.latitud)) || !Number.isFinite(Number(refugio.longitud))) return;
            const marcador = L.circleMarker(
                [refugio.latitud, refugio.longitud],
                {
                    color: '#4f7a32',
                    fillColor: '#4f7a32',
                    fillOpacity: 0.85,
                    radius: 9,
                },
            ).addTo(mapaPublico);
            const contenido = document.createElement('div');
            const titulo = document.createElement('strong');
            titulo.textContent = refugio.nombre;
            const direccion = document.createElement('p');
            direccion.textContent = refugio.direccion || '';
            contenido.append(titulo, direccion);
            marcador.bindPopup(contenido);
            limites.push([refugio.latitud, refugio.longitud]);
        });
        reportes.forEach((reporte) => {
            if (!Number.isFinite(Number(reporte.latitud)) || !Number.isFinite(Number(reporte.longitud))) return;
            const marcador = L.circleMarker(
                [reporte.latitud, reporte.longitud],
                {
                    color: '#b3362f',
                    fillColor: '#b3362f',
                    fillOpacity: 0.85,
                    radius: 8,
                },
            ).addTo(mapaPublico);
            const contenido = document.createElement('div');
            const titulo = document.createElement('strong');
            titulo.textContent = reporte.titulo;
            const descripcion = document.createElement('p');
            descripcion.textContent = reporte.descripcion || '';
            const enlaceDetalle = document.createElement('a');
            enlaceDetalle.href = reporte.url;
            enlaceDetalle.textContent = 'Ver detalle y fotografía';
            contenido.append(titulo, descripcion, enlaceDetalle);
            marcador.bindPopup(contenido);
            limites.push([reporte.latitud, reporte.longitud]);
        });
        if (limites.length > 1) mapaPublico.fitBounds(limites, { padding: [24, 24] });
        const estado = document.getElementById('estado-mapa');
        if (!limites.length && estado) estado.textContent = 'Todavía no hay ubicaciones verificadas para mostrar.';
    }

    const selector = document.getElementById('mapa-selector');
    const mapaSelector = iniciarMapa(selector);
    if (mapaSelector) {
        refugios.forEach((refugio) => {
            if (Number.isFinite(Number(refugio.latitud)) && Number.isFinite(Number(refugio.longitud))) {
                L.circleMarker([refugio.latitud, refugio.longitud], { color: '#4f7a32', fillColor: '#4f7a32', fillOpacity: 0.75, radius: 8 }).addTo(mapaSelector).bindTooltip(refugio.nombre);
            }
        });
        const latitud = document.getElementById('latitud');
        const longitud = document.getElementById('longitud');
        const estado = document.getElementById('estado-coordenadas');
        let marcadorSeleccionado = null;
        const actualizar = (lat, lng) => {
            latitud.value = lat.toFixed(7);
            longitud.value = lng.toFixed(7);
            estado.textContent = `Ubicación seleccionada: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
            if (marcadorSeleccionado) mapaSelector.removeLayer(marcadorSeleccionado);
            marcadorSeleccionado = L.marker([lat, lng]).addTo(mapaSelector);
        };
        mapaSelector.on('click', (evento) => actualizar(evento.latlng.lat, evento.latlng.lng));
        if (latitud.value && longitud.value) {
            const lat = Number(latitud.value);
            const lng = Number(longitud.value);
            if (Number.isFinite(lat) && Number.isFinite(lng)) {
                mapaSelector.setView([lat, lng], 14);
                actualizar(lat, lng);
            }
        }
    }

    document.querySelectorAll('.mapa-comunitario, .mapa-selector').forEach((elemento) => {
        if (typeof L === 'undefined') elemento.textContent = 'No se pudo cargar el mapa. Revisa tu conexión a internet.';
    });
})();
