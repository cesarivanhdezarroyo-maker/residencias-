<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// La limpieza de archivos (Excel/imágenes de solicitudes finalizadas) ya
// NO corre automática -- se dejó solo como botón manual en el panel de
// admin ("Limpieza"). El comando de consola (solicitudes:limpiar) sigue
// disponible si algún día se quiere volver a programar.
