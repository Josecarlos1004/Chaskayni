// Permite registrar coordenadas comunitarias con un mapa o ingresarlas manualmente.
document.addEventListener('DOMContentLoaded', () => {
    const formularios = document.querySelectorAll('[data-formulario-comunidad]');
    if (!formularios.length) return;

    formularios.forEach((formulario) => {
        const mapaElemento = formulario.querySelector('[data-mapa-comunidad]');
        const latitud = formulario.querySelector('input[name="latitud"]');
        const longitud = formulario.querySelector('input[name="longitud"]');
        const estado = formulario.querySelector('[data-estado-mapa-comunidad]');
        const urlDatosUbicacion = formulario.dataset.urlDatosUbicacion;
        const camposGeograficos = {
            distrito: formulario.querySelector('input[name="distrito"]'),
            provincia: formulario.querySelector('input[name="provincia"]'),
            altitud_msnm: formulario.querySelector('input[name="altitud_msnm"]'),
        };
        const camposEditados = new Set();
        if (!mapaElemento || !latitud || !longitud || !estado) return;

        Object.entries(camposGeograficos).forEach(([nombre, campo]) => {
            campo?.addEventListener('input', () => camposEditados.add(nombre));
        });

        let mapa = null;
        let marcador = null;
        let solicitudUbicacion = 0;
        const centroCusco = [-13.532, -71.967];

        const coordenadasValidas = () => {
            const lat = Number(latitud.value);
            const lng = Number(longitud.value);
            return latitud.value !== '' && longitud.value !== ''
                && Number.isFinite(lat) && Number.isFinite(lng)
                && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180;
        };

        // Consulta nombres cercanos y elevación; se mantienen editables y aproximados.
        const completarDatosGeograficos = async (lat, lng) => {
            if (!urlDatosUbicacion) return;
            const identificadorSolicitud = ++solicitudUbicacion;
            estado.textContent = `Buscando datos aproximados para ${lat.toFixed(5)}, ${lng.toFixed(5)}…`;

            try {
                const parametros = new URLSearchParams({ latitud: lat, longitud: lng });
                const respuesta = await fetch(`${urlDatosUbicacion}?${parametros}`, {
                    headers: { Accept: 'application/json' },
                });
                if (!respuesta.ok) throw new Error('No se pudieron consultar los datos del lugar.');

                const datos = await respuesta.json();
                if (identificadorSolicitud !== solicitudUbicacion) return;

                Object.entries(camposGeograficos).forEach(([nombre, campo]) => {
                    const valor = datos[nombre];
                    if (campo && valor !== null && valor !== undefined && valor !== '' && !camposEditados.has(nombre)) {
                        campo.value = valor;
                    }
                });

                const encontroDatos = Object.values(datos).some((valor) => valor !== null && valor !== undefined && valor !== '');
                estado.textContent = encontroDatos
                    ? 'Distrito, provincia y altitud aproximados; comprueba los datos antes de guardar.'
                    : 'No se encontraron datos para este punto. Puedes completar los campos manualmente.';
            } catch {
                if (identificadorSolicitud === solicitudUbicacion) {
                    estado.textContent = 'No se pudieron consultar los datos del lugar. Las coordenadas sí se guardarán; completa los demás campos manualmente.';
                }
            }
        };

        const marcarUbicacion = (lat, lng, centrar = true, consultarDatos = true) => {
            latitud.value = lat.toFixed(7);
            longitud.value = lng.toFixed(7);
            estado.textContent = `Coordenadas seleccionadas: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;

            if (mapa) {
                if (marcador) mapa.removeLayer(marcador);
                marcador = L.marker([lat, lng]).addTo(mapa);
                if (centrar) mapa.setView([lat, lng], 14);
            }

            if (consultarDatos) completarDatosGeograficos(lat, lng);
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
                marcarUbicacion(Number(latitud.value), Number(longitud.value), false, false);
            } else {
                estado.textContent = 'Haz clic en el mapa para seleccionar el punto exacto de la comunidad.';
            }
        };

        const detalleContenedor = mapaElemento.closest('details');
        if (detalleContenedor) {
            detalleContenedor.addEventListener('toggle', (evento) => {
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
                    solicitudUbicacion++;
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
