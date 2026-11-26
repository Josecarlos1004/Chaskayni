// Consulta automáticamente el pronóstico y vincula la ubicación elegida con la comunidad.
document.addEventListener('DOMContentLoaded', () => {
    const panelClima = document.querySelector('[data-panel-clima]');
    const selectorComunidad = document.querySelector('[data-seleccionar-comunidad]');
    const panelUbicacion = document.querySelector('[data-ubicacion-clima]');
    if (!panelClima || !selectorComunidad || !panelUbicacion) return;

    const botonConsultar = panelClima.querySelector('[data-consultar-clima]');
    const estadoClima = panelClima.querySelector('[data-clima-estado]');
    const datosClima = panelClima.querySelector('[data-datos-clima]');
    const formularioRegistro = document.querySelector('[data-registro-comunidad]');
    const temperatura = document.querySelector('input[name="temperatura_c"]');
    const campoLatitud = document.querySelector('[data-latitud-clima]');
    const campoLongitud = document.querySelector('[data-longitud-clima]');
    const botonPublicar = temperatura?.form?.querySelector('button[type="submit"]');
    let pronosticoConsultado = false;

    const actualizarDisponibilidad = () => {
        if (pronosticoConsultado && botonPublicar) {
            botonPublicar.disabled = Number(temperatura.value) >= 0;
        }
    };
    const estadoTemperatura = document.querySelector('[data-temperatura-autocompletada]');
    const mapaElemento = panelUbicacion.querySelector('[data-mapa-pronostico]');
    const botonGps = panelUbicacion.querySelector('[data-usar-gps]');
    const botonGuardarUbicacion = panelUbicacion.querySelector('[data-guardar-ubicacion]');
    const estadoUbicacion = panelUbicacion.querySelector('[data-estado-ubicacion]');
    const textoCoordenadas = panelUbicacion.querySelector('[data-coordenadas-seleccionadas]');
    let mapa = null;
    let marcador = null;
    let coordenadasPendientes = campoLatitud?.value && campoLongitud?.value
        ? { latitud: Number(campoLatitud.value), longitud: Number(campoLongitud.value) }
        : null;
    let solicitudClima = null;

    const opcionSeleccionada = () => selectorComunidad.options[selectorComunidad.selectedIndex];
    const coordenadasGuardadas = () => {
        const opcion = opcionSeleccionada();
        const lat = Number(opcion?.dataset.latitud);
        const lng = Number(opcion?.dataset.longitud);
        return opcion?.dataset.latitud && opcion?.dataset.longitud
            && Number.isFinite(lat) && Number.isFinite(lng)
            ? { latitud: lat, longitud: lng }
            : null;
    };

    const coordenadasSeleccionadas = () => coordenadasPendientes || coordenadasGuardadas();

    const guardarCoordenadasEnFormulario = (latitud, longitud) => {
        campoLatitud.value = Number(latitud).toFixed(7);
        campoLongitud.value = Number(longitud).toFixed(7);
    };

    const mostrarCoordenadas = (latitud, longitud, prefijo = 'Coordenadas seleccionadas') => {
        textoCoordenadas.textContent = prefijo + ': ' + Number(latitud).toFixed(6) + ', ' + Number(longitud).toFixed(6);
    };
    const actualizarMarcador = (latitud, longitud, centrar = true) => {
        if (!mapa) return;
        if (marcador) mapa.removeLayer(marcador);
        marcador = L.marker([latitud, longitud]).addTo(mapa);
        if (centrar) mapa.setView([latitud, longitud], 14);
    };

    const iniciarMapa = () => {
        if (mapa || typeof L === 'undefined') return;
        const coordenadas = coordenadasGuardadas();
        mapa = L.map(mapaElemento).setView(
            coordenadas ? [coordenadas.latitud, coordenadas.longitud] : [-13.532, -71.967],
            coordenadas ? 14 : 11,
        );
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap',
        }).addTo(mapa);
        mapa.on('click', (evento) => {
            coordenadasPendientes = {
                latitud: evento.latlng.lat,
                longitud: evento.latlng.lng,
            };
            actualizarMarcador(coordenadasPendientes.latitud, coordenadasPendientes.longitud, false);
            guardarCoordenadasEnFormulario(coordenadasPendientes.latitud, coordenadasPendientes.longitud);
            mostrarCoordenadas(coordenadasPendientes.latitud, coordenadasPendientes.longitud, 'Punto seleccionado');
            estadoUbicacion.textContent = 'Ubicación seleccionada. Consulta el clima o publícala sin asignar una comunidad.';
            botonConsultar.disabled = false;
            botonGuardarUbicacion.hidden = !selectorComunidad.value;
        });
        if (coordenadas) actualizarMarcador(coordenadas.latitud, coordenadas.longitud, false);
    };

    const descripcionTiempo = (codigo) => {
        if (codigo === 0) return 'Despejado';
        if ([1, 2].includes(codigo)) return 'Parcialmente nublado';
        if (codigo === 3) return 'Mayormente nublado';
        if ([45, 48].includes(codigo)) return 'Neblina';
        if ([51, 53, 55, 56, 57].includes(codigo)) return 'Llovizna';
        if ([61, 63, 65, 66, 67, 80, 81, 82].includes(codigo)) return 'Lluvia';
        if ([71, 73, 75, 77, 85, 86].includes(codigo)) return 'Nieve';
        if ([95, 96, 99].includes(codigo)) return 'Tormenta';
        return 'Condición meteorológica variable';
    };

    const iconoTiempo = (codigo) => {
        if (codigo === 0) return '☀️';
        if ([1, 2].includes(codigo)) return '🌤️';
        if (codigo === 3) return '☁️';
        if ([45, 48].includes(codigo)) return '🌫️';
        if ([51, 53, 55, 56, 57, 61, 63, 65, 66, 67, 80, 81, 82].includes(codigo)) return '🌧️';
        if ([71, 73, 75, 77, 85, 86].includes(codigo)) return '🌨️';
        if ([95, 96, 99].includes(codigo)) return '⛈️';
        return '☁️';
    };

    const mostrarEstadoClima = (mensaje, esError = false) => {
        estadoClima.textContent = mensaje;
        estadoClima.classList.toggle('es-error', esError);
        estadoClima.hidden = false;
    };

    const formatoHora = (hora) => {
        const numero = Number(hora.split(':')[0]);
        const periodo = numero < 12 ? 'a. m.' : 'p. m.';
        return `${numero % 12 || 12} ${periodo}`;
    };

    const mostrarPronosticoPorHora = (horario) => {
        const contenedor = panelClima.querySelector('[data-pronostico-horas]');
        contenedor.replaceChildren();
        horario.forEach((hora) => {
            const tarjeta = document.createElement('div');
            tarjeta.className = 'pronostico-hora';
            const etiquetaHora = document.createElement('span');
            etiquetaHora.className = 'pronostico-hora-tiempo';
            etiquetaHora.textContent = formatoHora(hora.hora);
            const icono = document.createElement('span');
            icono.className = 'pronostico-hora-icono';
            icono.setAttribute('aria-hidden', 'true');
            icono.textContent = iconoTiempo(hora.codigo_tiempo);
            const grados = document.createElement('strong');
            grados.textContent = `${hora.temperatura}°`;
            const lluvia = document.createElement('small');
            lluvia.textContent = hora.probabilidad_lluvia === null ? '—' : `${hora.probabilidad_lluvia}% lluvia`;
            tarjeta.append(etiquetaHora, icono, grados, lluvia);
            contenedor.append(tarjeta);
        });
    };

    const consultarClima = async () => {
        const coordenadas = coordenadasSeleccionadas();
        if (!coordenadas) {
            pronosticoConsultado = false;
            if (botonPublicar) botonPublicar.disabled = false;
            botonConsultar.disabled = true;
            mostrarEstadoClima('Elige un punto en el mapa o activa el GPS para consultar el pronóstico.');
            datosClima.hidden = true;
            temperatura.value = '';
            return;
        }
        pronosticoConsultado = false;
        if (botonPublicar) botonPublicar.disabled = false;
        solicitudClima?.abort();
        solicitudClima = new AbortController();
        botonConsultar.disabled = true;
        botonConsultar.textContent = 'Consultando…';
        datosClima.hidden = true;
        temperatura.value = '';
        estadoTemperatura.textContent = 'Esperando el pronóstico para completar la temperatura.';
        mostrarEstadoClima('Consultando el pronóstico para las coordenadas seleccionadas…');

        try {
            const url = new URL(panelClima.dataset.urlClima, window.location.origin);
            url.searchParams.set('latitud', coordenadas.latitud);
            url.searchParams.set('longitud', coordenadas.longitud);
            const respuesta = await fetch(url, {
                cache: 'no-store',
                headers: { Accept: 'application/json' },
                signal: solicitudClima.signal,
            });
            const resultado = await respuesta.json();
            if (!respuesta.ok) throw new Error(resultado.mensaje || 'No se pudo consultar el clima.');

            const nombreElegido = selectorComunidad.value ? opcionSeleccionada().textContent.trim().split('·')[0].trim() : 'Punto seleccionado';
            panelClima.querySelector('[data-titulo-clima]').textContent = nombreElegido.split('·')[0].trim();
            panelClima.querySelector('[data-clima-ubicacion]').textContent = `Coordenadas: ${Number(resultado.latitud).toFixed(6)}, ${Number(resultado.longitud).toFixed(6)} · Datos: ${resultado.actualizado_en}`;
            panelClima.querySelector('[data-temperatura-actual]').textContent = `${resultado.temperatura_actual}°`;
            panelClima.querySelector('[data-descripcion-clima]').textContent = descripcionTiempo(resultado.codigo_tiempo);
            panelClima.querySelector('[data-icono-clima]').textContent = iconoTiempo(resultado.codigo_tiempo);
            panelClima.querySelector('[data-maxima-hoy]').textContent = resultado.maxima_hoy === null ? '—' : `${resultado.maxima_hoy}°`;
            panelClima.querySelector('[data-minima-hoy]').textContent = resultado.minima_hoy === null ? '—' : `${resultado.minima_hoy}°`;
            panelClima.querySelector('[data-temperatura-minima]').textContent = `${resultado.minima_24h}°`;
            panelClima.querySelector('[data-hora-minima]').textContent = `cerca de ${formatoHora(resultado.hora_minima)}`;
            panelClima.querySelector('[data-sensacion-termica]').textContent = resultado.sensacion_termica === null ? '—' : `${resultado.sensacion_termica}°`;
            panelClima.querySelector('[data-humedad]').textContent = resultado.humedad === null ? '—' : `${resultado.humedad}%`;
            panelClima.querySelector('[data-viento]').textContent = resultado.viento_kmh === null ? '—' : `${resultado.viento_kmh} km/h`;
            mostrarPronosticoPorHora(resultado.horario || []);
            temperatura.value = resultado.minima_24h;
            pronosticoConsultado = true;
            actualizarDisponibilidad();
            estadoTemperatura.textContent = Number(resultado.minima_24h) >= 0
                ? 'El pronóstico no prevé temperaturas bajo cero para las próximas 24 horas; no se habilita publicar una alerta de helada.'
                : `Temperatura completada automáticamente: mínima prevista de ${resultado.minima_24h} °C en las próximas 24 horas.`;
            datosClima.hidden = false;
            estadoClima.hidden = true;
            const ubicacion = coordenadasSeleccionadas();
            mostrarCoordenadas(ubicacion.latitud, ubicacion.longitud);
            estadoUbicacion.textContent = selectorComunidad.value ? 'Pronóstico cargado para la comunidad seleccionada.' : 'Pronóstico cargado para el punto elegido; la comunidad queda opcional.';
        } catch (error) {
            if (error.name !== 'AbortError') {
                pronosticoConsultado = false;
                if (botonPublicar) botonPublicar.disabled = false;
                const mensaje = ['SyntaxError', 'TypeError'].includes(error.name)
                    ? 'No hay conexión con el servicio meteorológico. Puedes ingresar la temperatura manualmente.'
                    : error.message;
                mostrarEstadoClima(mensaje, true);
                estadoTemperatura.textContent = 'Si el pronóstico no está disponible, ingresa la temperatura manualmente.';
            }
        } finally {
            botonConsultar.disabled = !coordenadasSeleccionadas();
            botonConsultar.textContent = 'Actualizar clima';
        }
    };

    temperatura?.addEventListener('input', actualizarDisponibilidad);

    const guardarCoordenadas = async (latitud, longitud, origen) => {
        const comunidadId = selectorComunidad.value;
        if (!comunidadId) throw new Error('Selecciona primero una comunidad.');
        const boton = origen === 'GPS' ? botonGps : botonGuardarUbicacion;
        const textoOriginal = boton.textContent;
        boton.disabled = true;
        boton.textContent = 'Guardando…';
        estadoUbicacion.textContent = `Guardando coordenadas obtenidas del ${origen}…`;

        try {
            const url = panelUbicacion.dataset.urlGuardar.replace('__ID__', encodeURIComponent(comunidadId));
            const respuesta = await fetch(url, {
                method: 'PUT',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ latitud, longitud }),
            });
            const resultado = await respuesta.json();
            if (!respuesta.ok) throw new Error(resultado.message || resultado.mensaje || 'No se pudieron guardar las coordenadas.');

            const opcion = opcionSeleccionada();
            opcion.dataset.latitud = resultado.latitud;
            opcion.dataset.longitud = resultado.longitud;
            guardarCoordenadasEnFormulario(resultado.latitud, resultado.longitud);
            coordenadasPendientes = null;
            botonGuardarUbicacion.hidden = true;
            mostrarCoordenadas(resultado.latitud, resultado.longitud);
            estadoUbicacion.classList.remove('es-error');
            estadoUbicacion.textContent = `Ubicación actualizada desde ${origen}. Consultando el pronóstico.`;
            actualizarMarcador(Number(resultado.latitud), Number(resultado.longitud));
            await consultarClima();
        } finally {
            boton.disabled = false;
            boton.textContent = textoOriginal;
        }
    };

    const actualizarSeleccion = () => {
        const coordenadas = coordenadasGuardadas();
        botonGps.disabled = false;
        if (coordenadas) {
            coordenadasPendientes = null;
            guardarCoordenadasEnFormulario(coordenadas.latitud, coordenadas.longitud);
            mostrarCoordenadas(coordenadas.latitud, coordenadas.longitud, 'Coordenadas de la comunidad');
            estadoUbicacion.textContent = 'Se usará la ubicación guardada de esta comunidad.';
            botonGuardarUbicacion.hidden = true;
            botonConsultar.disabled = false;
            actualizarMarcador(coordenadas.latitud, coordenadas.longitud);
            consultarClima();
            return;
        }

        coordenadasPendientes = null;
        campoLatitud.value = '';
        campoLongitud.value = '';
        textoCoordenadas.textContent = selectorComunidad.value
            ? 'Esta comunidad no tiene coordenadas; elige un punto en el mapa o usa GPS.'
            : 'Usa GPS o marca cualquier punto del mapa; no hace falta elegir comunidad.';
        estadoUbicacion.textContent = 'El punto elegido se usará solo para esta alerta. Si asignas una comunidad, puedes guardar el punto en ella.';
        botonGuardarUbicacion.hidden = true;
        botonConsultar.disabled = true;
        if (marcador && mapa) mapa.removeLayer(marcador);
        marcador = null;
        datosClima.hidden = true;
        temperatura.value = '';
        mostrarEstadoClima('Elige un punto en el mapa o activa el GPS para consultar el clima cercano.');
    };
    iniciarMapa();
    if (typeof L === 'undefined') mapaElemento.textContent = 'No se pudo cargar el mapa. Comprueba la conexión a internet.';
    if (selectorComunidad.value) {
        actualizarSeleccion();
    } else if (coordenadasPendientes) {
        mostrarCoordenadas(coordenadasPendientes.latitud, coordenadasPendientes.longitud, 'Punto seleccionado');
        actualizarMarcador(coordenadasPendientes.latitud, coordenadasPendientes.longitud);
        botonConsultar.disabled = false;
    }
    selectorComunidad.addEventListener('change', actualizarSeleccion);
    botonConsultar.addEventListener('click', consultarClima);

    botonGps.addEventListener('click', () => {
        if (!navigator.geolocation) {
            estadoUbicacion.textContent = 'Este navegador no permite obtener ubicación GPS.';
            return;
        }
        botonGps.disabled = true;
        estadoUbicacion.textContent = 'Solicitando permiso y ubicación al dispositivo…';
        navigator.geolocation.getCurrentPosition(
            async (posicion) => {
                const { latitude, longitude, accuracy } = posicion.coords;
                coordenadasPendientes = { latitud: latitude, longitud: longitude };
                guardarCoordenadasEnFormulario(latitude, longitude);
                mostrarCoordenadas(latitude, longitude, 'GPS · precisión ±' + Math.round(accuracy) + ' m');
                actualizarMarcador(latitude, longitude);
                botonConsultar.disabled = false;
                botonGuardarUbicacion.hidden = !selectorComunidad.value;
                estadoUbicacion.textContent = 'Punto GPS listo. Consultando el pronóstico para esta alerta.';
                try {
                    await consultarClima();
                } finally {
                    botonGps.disabled = false;
                }
            },
            (error) => {
                botonGps.disabled = false;
                const mensajes = {
                    1: 'No se autorizó el acceso a la ubicación. Puedes marcar el punto en el mapa.',
                    2: 'El dispositivo no pudo determinar la ubicación. Prueba seleccionarla en el mapa.',
                    3: 'La ubicación tardó demasiado. Intenta nuevamente o selecciona el punto en el mapa.',
                };
                estadoUbicacion.textContent = mensajes[error.code] || 'No se pudo obtener la ubicación GPS.';
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 },
        );
    });

    botonGuardarUbicacion.addEventListener('click', async () => {
        if (!coordenadasPendientes) return;
        try {
            await guardarCoordenadas(coordenadasPendientes.latitud, coordenadasPendientes.longitud, 'mapa');
        } catch (error) {
            estadoUbicacion.textContent = error.message;
            estadoUbicacion.classList.add('es-error');
        }
    });

    formularioRegistro?.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        const boton = formularioRegistro.querySelector('button[type="submit"]');
        const estado = formularioRegistro.querySelector('[data-registro-estado]');
        const textoOriginal = boton.textContent;
        boton.disabled = true;
        boton.textContent = 'Registrando…';
        estado.hidden = false;
        estado.classList.remove('es-error');
        estado.textContent = 'Guardando la nueva comunidad…';

        try {
            const respuesta = await fetch(formularioRegistro.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': formularioRegistro.querySelector('input[name="_token"]').value,
                },
                body: new FormData(formularioRegistro),
            });
            const resultado = await respuesta.json();
            if (!respuesta.ok) {
                const primerError = Object.values(resultado.errors || {}).flat()[0];
                throw new Error(primerError || resultado.message || 'No se pudo registrar la comunidad.');
            }

            const opcion = new Option(
                `${resultado.nombre}${resultado.altitud_msnm ? ` · ${resultado.altitud_msnm} m s. n. m.` : ''}`,
                resultado.id,
                true,
                true,
            );
            opcion.dataset.latitud = resultado.latitud ?? '';
            opcion.dataset.longitud = resultado.longitud ?? '';
            selectorComunidad.add(opcion);
            selectorComunidad.dispatchEvent(new Event('change', { bubbles: true }));
            formularioRegistro.reset();
            formularioRegistro.querySelector('input[name="provincia"]').value = 'Cusco';
            estado.textContent = resultado.latitud
                ? 'Comunidad registrada y seleccionada. Pronóstico en consulta.'
                : 'Comunidad registrada. Usa GPS o selecciona su ubicación en el mapa para ver el pronóstico.';
        } catch (error) {
            estado.classList.add('es-error');
            estado.textContent = ['SyntaxError', 'TypeError'].includes(error.name)
                ? 'No se pudo conectar para registrar la comunidad. Inténtalo nuevamente.'
                : error.message || 'No se pudo registrar la comunidad.';
        } finally {
            boton.disabled = false;
            boton.textContent = textoOriginal;
        }
    });
});
