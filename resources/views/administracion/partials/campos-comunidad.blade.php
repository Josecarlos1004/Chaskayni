<label>
    Nombre de la comunidad
    <input name="nombre" value="{{ $nombre }}" maxlength="120" required />
</label>

<div class="fila-formulario">
    <label>
        Distrito (opcional)
        <input name="distrito" value="{{ $distrito }}" maxlength="120" />
    </label>
    <label>
        Provincia
        <input name="provincia" value="{{ $provincia }}" maxlength="120" required />
    </label>
</div>

<label>
    Altitud (m s. n. m., aproximada)
    <input type="number" name="altitud_msnm" min="0" max="8000" step="1" value="{{ $altitud }}" />
</label>

<div class="campo-ubicacion-comunidad">
    <span class="etiqueta-campo">Ubicación para el pronóstico</span>
    <p class="texto-atenuado">Marca el punto en el mapa. Distrito, provincia y altitud se completarán de forma aproximada; revísalos antes de guardar.</p>
    <small class="texto-atenuado">Distrito y provincia: Photon y OpenStreetMap. Altitud: estimación del modelo Copernicus DEM.</small>
    <div class="mapa-selector-comunidad" data-mapa-comunidad role="region" aria-label="Selecciona la ubicación de la comunidad"></div>
    <p class="estado-mapa-comunidad" data-estado-mapa-comunidad role="status" aria-live="polite">Haz clic en el mapa para seleccionar la ubicación.</p>
    <div class="fila-formulario fila-coordenadas-comunidad">
        <label>
            Latitud
            <input type="number" name="latitud" step="any" min="-90" max="90" value="{{ $latitud }}" />
        </label>
        <label>
            Longitud
            <input type="number" name="longitud" step="any" min="-180" max="180" value="{{ $longitud }}" />
        </label>
    </div>
</div>