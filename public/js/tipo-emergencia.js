// Muestra el campo libre solo si se selecciona otra emergencia.
document.querySelectorAll('[data-tipo-emergencia]').forEach((selector) => {
    const contenedor = selector.closest('form')?.querySelector('[data-campo-tipo-emergencia]');
    const campo = contenedor?.querySelector('input[name="tipo_personalizado"]');

    if (!contenedor || !campo) return;

    const actualizarCampo = () => {
        const esOtro = selector.value === 'otro';
        contenedor.hidden = !esOtro;
        campo.required = esOtro;
    };

    selector.addEventListener('change', actualizarCampo);
    actualizarCampo();
});