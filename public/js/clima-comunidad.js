// Presenta el pronóstico de la comunidad y permite añadir una comunidad desde el formulario.
document.addEventListener('DOMContentLoaded', () => {
    const panelClima = document.querySelector('[data-panel-clima]');
    const selectorComunidad = document.querySelector('[data-seleccionar-comunidad]');

    if (!panelClima || !selectorComunidad) return;

    const botonConsultar = panelClima.querySelector('[data-consultar-clima]');
    const estadoClima = panelClima.querySelector('[data-clima-estado]');
    const datosClima = panelClima.querySelector('[data-datos-clima]');
    const formularioRegistro = document.querySelector('[data-registro-comunidad]');
    const temperatura = document.querySelector('input[name="temperatura_c"]');
    let minimaDisponible = null;
    let solicitudClima = null;

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
        const comunidadId = selectorComunidad.value;
        if (!comunidadId) {
            mostrarEstadoClima('Selecciona una comunidad para consultar las condiciones cercanas.');
            return;
        }

        solicitudClima?.abort();
        solicitudClima = new AbortController();
        botonConsultar.disabled = true;
        botonConsultar.textContent = 'Consultando…';
        datosClima.hidden = true;
        minimaDisponible = null;
        mostrarEstadoClima('Buscando ubicación y pronóstico…');

        try {
            const url = panelClima.dataset.urlClima.replace('__ID__', encodeURIComponent(comunidadId));
            const respuesta = await fetch(url, {
                headers: { Accept: 'application/json' },
                signal: solicitudClima.signal,
            });
            const resultado = await respuesta.json();
            if (!respuesta.ok) throw new Error(resultado.mensaje || 'No se pudo consultar el clima.');

            const nombreElegido = selectorComunidad.options[selectorComunidad.selectedIndex].textContent.trim();
            panelClima.querySelector('[data-titulo-clima]').textContent = nombreElegido.split('·')[0].trim();
            panelClima.querySelector('[data-clima-ubicacion]').textContent = `Coordenadas de la comunidad: ${Number(resultado.latitud).toFixed(5)}, ${Number(resultado.longitud).toFixed(5)} · Actualizado: ${resultado.actualizado_en}`;
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
            minimaDisponible = resultado.minima_24h;
            datosClima.hidden = false;
            estadoClima.hidden = true;
        } catch (error) {
            if (error.name !== 'AbortError') {
                const mensaje = ['SyntaxError', 'TypeError'].includes(error.name)
                    ? 'No hay conexión con el servicio meteorológico. Ingresa la temperatura manualmente.'
                    : error.message;
                mostrarEstadoClima(mensaje, true);
            }
        } finally {
            botonConsultar.disabled = !selectorComunidad.value;
            botonConsultar.textContent = 'Actualizar clima';
        }
    };

    selectorComunidad.addEventListener('change', consultarClima);
    botonConsultar.addEventListener('click', consultarClima);
    panelClima.querySelector('[data-usar-temperatura]').addEventListener('click', () => {
        if (minimaDisponible === null || !temperatura) return;
        temperatura.value = minimaDisponible;
        temperatura.dispatchEvent(new Event('input', { bubbles: true }));
        temperatura.focus();
    });

    if (selectorComunidad.value) {
        botonConsultar.disabled = false;
        consultarClima();
    }

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
            selectorComunidad.add(opcion);
            selectorComunidad.dispatchEvent(new Event('change', { bubbles: true }));
            formularioRegistro.reset();
            formularioRegistro.querySelector('input[name="provincia"]').value = 'Cusco';
            estado.textContent = 'Comunidad registrada y seleccionada. Consultando su clima cercano.';
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