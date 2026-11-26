// Permite elegir un único formulario de publicación y conserva el detalle del tipo de emergencia.
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

const botonesFlujo = document.querySelectorAll('[data-seleccionar-flujo]');
const panelesFlujo = document.querySelectorAll('[data-panel-flujo]');

const mostrarFlujo = (flujoElegido) => {
    panelesFlujo.forEach((panel) => {
        panel.hidden = panel.dataset.panelFlujo !== flujoElegido;
    });

    botonesFlujo.forEach((boton) => {
        const seleccionado = boton.dataset.seleccionarFlujo === flujoElegido;
        boton.setAttribute('aria-pressed', String(seleccionado));
    });
    document.dispatchEvent(new CustomEvent('flujo-publicacion-cambio', { detail: { flujo: flujoElegido } }));
};

botonesFlujo.forEach((boton) => {
    boton.addEventListener('click', () => {
        mostrarFlujo(boton.dataset.seleccionarFlujo);
    });
});

const flujoInicial = document.querySelector('[data-panel-flujo]:not([hidden])')?.dataset.panelFlujo;
if (flujoInicial) mostrarFlujo(flujoInicial);
