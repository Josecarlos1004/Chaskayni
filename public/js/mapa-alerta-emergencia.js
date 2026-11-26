(() => {
    const contenedor = document.querySelector('[data-mapa-alerta-emergencia]');
    if (!contenedor) return;

    const mapaElemento = contenedor.querySelector('[data-mapa-emergencia]');
    const estado = contenedor.querySelector('[data-estado-mapa-alerta]');
    const botonGps = contenedor.querySelector('[data-gps-alerta]');
    const latitud = contenedor.querySelector('input[name="latitud"]');
    const longitud = contenedor.querySelector('input[name="longitud"]');
    const selectorComunidad = contenedor.closest('form')?.querySelector('select[name="comunidad_id"]');
    const coordenadasCusco = [-13.532, -71.967];
    let mapa = null;
    let marcador = null;

    const actualizarCampos = (lat, lon, mensaje) => {
        latitud.value = Number(lat).toFixed(7);
        longitud.value = Number(lon).toFixed(7);
        estado.textContent = mensaje;
        if (mapa) {
            const punto = [Number(lat), Number(lon)];
            if (!marcador) {
                marcador = L.marker(punto, { draggable: true }).addTo(mapa);
                marcador.on('dragend', () => {
                    const posicion = marcador.getLatLng();
                    latitud.value = posicion.lat.toFixed(7);
                    longitud.value = posicion.lng.toFixed(7);
                    estado.textContent = 'Punto ajustado en el mapa.';
                });
            } else {
                marcador.setLatLng(punto);
            }
            mapa.setView(punto, 15);
        }
    };

    const comunidadSeleccionada = () => {
        const opcion = selectorComunidad?.selectedOptions[0];
        const lat = Number(opcion?.dataset.latitud);
        const lon = Number(opcion?.dataset.longitud);
        return Number.isFinite(lat) && Number.isFinite(lon) && opcion?.dataset.latitud && opcion?.dataset.longitud
            ? [lat, lon]
            : null;
    };

    const crearMapa = () => {
        if (mapa || !window.L || !mapaElemento || contenedor.closest('[hidden]')) return;
        const latInicial = Number(latitud.value);
        const lonInicial = Number(longitud.value);
        const guardadas = Number.isFinite(latInicial) && Number.isFinite(lonInicial)
            && latitud.value !== '' && longitud.value !== '';
        const puntoComunidad = comunidadSeleccionada();
        const centro = guardadas ? [latInicial, lonInicial] : (puntoComunidad || coordenadasCusco);

        mapa = L.map(mapaElemento, { scrollWheelZoom: false }).setView(centro, guardadas || puntoComunidad ? 14 : 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap',
        }).addTo(mapa);

        if (guardadas) actualizarCampos(latInicial, lonInicial, 'Se cargó la ubicación guardada.');
        else if (puntoComunidad) actualizarCampos(puntoComunidad[0], puntoComunidad[1], 'Se cargó la ubicación registrada de la comunidad.');

        mapa.on('click', (evento) => {
            actualizarCampos(evento.latlng.lat, evento.latlng.lng, 'Punto seleccionado en el mapa.');
        });
        mapa.whenReady(() => mapa.invalidateSize());
    };

    crearMapa();

    selectorComunidad?.addEventListener('change', () => {
        const punto = comunidadSeleccionada();
        if (punto) actualizarCampos(punto[0], punto[1], 'Se cargó la ubicación registrada de la comunidad.');
        else {
            latitud.value = '';
            longitud.value = '';
            if (marcador) mapa.removeLayer(marcador);
            marcador = null;
            estado.textContent = 'Esta comunidad no tiene coordenadas registradas. Marca el lugar en el mapa.';
        }
    });

    botonGps?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            estado.textContent = 'Este navegador no permite consultar la ubicación GPS.';
            return;
        }
        estado.textContent = 'Consultando ubicación GPS…';
        navigator.geolocation.getCurrentPosition(
            ({ coords }) => actualizarCampos(coords.latitude, coords.longitude, 'Ubicación GPS seleccionada.'),
            () => { estado.textContent = 'No se pudo obtener el GPS. Revisa el permiso del navegador o marca el punto en el mapa.'; },
            { enableHighAccuracy: true, timeout: 12000, maximumAge: 60000 },
        );
    });

    const actualizarMarcadorDesdeCampos = () => {
        const lat = Number(latitud.value);
        const lon = Number(longitud.value);
        if (latitud.value !== '' && longitud.value !== '' && Number.isFinite(lat) && Number.isFinite(lon)) {
            actualizarCampos(lat, lon, 'Coordenadas actualizadas.');
        }
    };
    latitud.addEventListener('change', actualizarMarcadorDesdeCampos);
    longitud.addEventListener('change', actualizarMarcadorDesdeCampos);

    document.addEventListener('flujo-publicacion-cambio', (evento) => {
        if (evento.detail?.flujo !== 'otras-emergencias') return;
        crearMapa();
        requestAnimationFrame(() => mapa?.invalidateSize());
    });

    if (!window.L) estado.textContent = 'No se pudo cargar el mapa. Puedes escribir las coordenadas manualmente.';
})();