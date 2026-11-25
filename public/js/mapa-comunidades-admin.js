// Permite registrar coordenadas comunitarias con un mapa o ingresarlas manualmente.
document.addEventListener('DOMContentLoaded', () => {
    const formularios = document.querySelectorAll('[data-formulario-comunidad]');
    if (!formularios.length) return;

    formularios.forEach((formulario) => {
        const mapaElemento = formulario.querySelector('[data-mapa-comunidad]');
        const latitud = formulario.querySelector('input[name="latitud"]');
        const longitud = formulario.querySelector('input[name="longitud"]');
        const estado = formulario.querySelector('[data-estado-mapa-comunidad]');
        if (!mapaElemento || !latitud || !longitud || !estado) return;

        let mapa = null;
        let marcador = null;
        const centroCusco = [-13.532, -71.967];

        const coordenadasValidas = () => {
            const lat = Number(latitud.value);
            const lng = Number(longitud.value);
            return latitud.value !== '' && longitud.value !== ''
                && Number.isFinite(lat) && Number.isFinite(lng)
                && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180;
        };

        const marcarUbicacion = (lat, lng, centrar = true) => {
            latitud.value = lat.toFixed(7);
            longitud.value = lng.toFixed(7);
            estado.textContent = `Coordenadas guardadas: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;

            if (!mapa) return;
            if (marcador) mapa.removeLayer(marcador);
            marcador = L.marker([lat, lng]).addTo(mapa);
            if (centrar) mapa.setView([lat, lng], 14);
        };

        const iniciarMapa = () => {
            if (mapa || typeof L === 'undefined') return;
            const coordenadasIniciales = coordenadasValidas();
            const centroInicial = coordenadasIniciales
                ? [Number(latitud.value), Number(longitud.value)]
                : centroCusco;

            mapa = L.map(mapaElemento).setView(centroInicial, coordenadasIniciales ? 14 : 11);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap',
            }).addTo(mapa);
            mapa.on('click', (evento) => marcarUbicacion(evento.latlng.lat, evento.latlng.lng));

            if (coordenadasIniciales) {
                marcarUbicacion(Number(latitud.value), Number(longitud.value), false);
            } else {
                estado.textContent = 'Haz clic en el mapa para guardar el punto exacto de la comunidad.';
            }
        };

        if (mapaElemento.closest('details')) {
            mapaElemento.closest('details').addEventListener('toggle', (evento) => {
                if (evento.target.open) {
                    iniciarMapa();
                    window.setTimeout(() => mapa?.invalidateSize(), 100);
                }
            });
        } else {
            iniciarMapa();
        }

        [latitud, longitud].forEach((campo) => {
            campo.addEventListener('change', () => {
                if (coordenadasValidas()) {
                    iniciarMapa();
                    marcarUbicacion(Number(latitud.value), Number(longitud.value));
                } else if (latitud.value === '' && longitud.value === '') {
                    estado.textContent = 'No hay coordenadas registradas. El pronóstico estará desactivado hasta guardar una ubicación.';
                    if (marcador && mapa) mapa.removeLayer(marcador);
                    marcador = null;
                }
            });
        });
    });

    document.querySelectorAll('[data-mapa-comunidad]').forEach((elemento) => {
        if (typeof L === 'undefined') {
            elemento.textContent = 'No se pudo cargar el mapa. Ingresa las coordenadas manualmente.';
        }
    });
});

