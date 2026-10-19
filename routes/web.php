<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\AdminSolicitudController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('login');
});

// EL "AGENTE DE TRÁNSITO"
Route::get('/dashboard', function () {
    if (Auth::user()->rol === 'admin') {
        return redirect()->route('admin.usuarios.index');
    }
    return redirect()->route('solicitudes.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// RUTAS DE USUARIO NORMAL
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/mis-solicitudes', [SolicitudController::class, 'index'])->name('solicitudes.index');
    Route::get('/solicitudes/nueva', [SolicitudController::class, 'create'])->name('solicitudes.create');
    Route::post('/solicitudes', [SolicitudController::class, 'store'])->name('solicitudes.store');
    
    Route::get('/solicitudes/{id}/descargar', [SolicitudController::class, 'descargarPdf'])->name('solicitudes.descargar');
    Route::get('/solicitudes/{id}/pdf', [SolicitudController::class, 'convertirAPdf'])->name('solicitudes.pdf');
    Route::get('/solicitudes/{id}/oficio', [SolicitudController::class, 'descargarOficio'])->name('solicitudes.oficio');
    Route::get('/solicitudes/{id}/editar', [SolicitudController::class, 'edit'])->name('solicitudes.edit');
    Route::put('/solicitudes/{id}', [SolicitudController::class, 'update'])->name('solicitudes.update');

    Route::get('/avisos', [\App\Http\Controllers\MensajeController::class, 'index'])->name('mensajes.index');
    Route::post('/avisos', [\App\Http\Controllers\MensajeController::class, 'store'])->name('mensajes.store');
    Route::patch('/avisos/{id}/confirmar', [\App\Http\Controllers\MensajeController::class, 'confirmar'])->name('mensajes.confirmar');

});

// RUTAS DEL ADMINISTRADOR
Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
    Route::get('/admin/usuarios', [AdminSolicitudController::class, 'index'])->name('admin.usuarios.index');
    Route::delete('/admin/usuarios/{id}', [AdminSolicitudController::class, 'eliminarUsuario'])->name('admin.usuarios.eliminar');
    Route::get('/admin/usuarios/{id}/solicitudes', [AdminSolicitudController::class, 'verSolicitudes'])->name('admin.solicitudes.usuario');
    Route::patch('/admin/usuarios/{id}/credenciales-plataforma', [AdminSolicitudController::class, 'actualizarCredencialesPlataforma'])->name('admin.usuarios.credenciales_plataforma');
    Route::patch('/admin/solicitudes/{id}/estado', [AdminSolicitudController::class, 'actualizarEstado'])->name('admin.solicitudes.estado');
    Route::delete('/admin/solicitudes/{id}', [AdminSolicitudController::class, 'eliminarSolicitud'])->name('admin.solicitudes.eliminar');

    Route::get('/admin/reportes', [AdminSolicitudController::class, 'reportes'])->name('admin.reportes.index');
    Route::get('/admin/reportes/exportar', [AdminSolicitudController::class, 'exportarReporte'])->name('admin.reportes.exportar');

    Route::get('/admin/limpieza', [AdminSolicitudController::class, 'limpiezaIndex'])->name('admin.limpieza.index');
    Route::post('/admin/limpieza', [AdminSolicitudController::class, 'limpiezaEjecutar'])->name('admin.limpieza.ejecutar');
    Route::get('/admin/solicitudes/{id}/pdf', [AdminSolicitudController::class, 'generarPdfAdmin'])->name('admin.solicitudes.pdf');
    Route::get('/admin/solicitudes/{id}/oficio', [AdminSolicitudController::class, 'descargarOficioAdmin'])->name('admin.solicitudes.oficio');

    Route::get('/admin/oficio', [AdminSolicitudController::class, 'configuracionOficioIndex'])->name('admin.oficio.index');
    Route::patch('/admin/oficio', [AdminSolicitudController::class, 'configuracionOficioActualizar'])->name('admin.oficio.actualizar');
    Route::get('/admin/configuracion-excel', [AdminSolicitudController::class, 'configuracionExcelIndex'])->name('admin.configuracion.excel');
    Route::patch('/admin/configuracion-excel', [AdminSolicitudController::class, 'configuracionExcelActualizar'])->name('admin.configuracion.excel.actualizar');

    Route::get('/admin/solicitudes/{id}/evidencia', [\App\Http\Controllers\AdminArchivoController::class, 'evidencia'])->name('admin.solicitudes.evidencia');
    Route::get('/admin/solicitudes/{id}/excel', [\App\Http\Controllers\AdminArchivoController::class, 'excel'])->name('admin.solicitudes.excel');

    Route::get('/admin/mensajes', [\App\Http\Controllers\AdminMensajeController::class, 'index'])->name('admin.mensajes.index');
    Route::get('/admin/mensajes/nuevo', [\App\Http\Controllers\AdminMensajeController::class, 'create'])->name('admin.mensajes.create');
    Route::post('/admin/mensajes', [\App\Http\Controllers\AdminMensajeController::class, 'store'])->name('admin.mensajes.store');
    Route::patch('/admin/mensajes/{id}/leido', [\App\Http\Controllers\AdminMensajeController::class, 'marcarLeido'])->name('admin.mensajes.leido');

});

require __DIR__.'/auth.php';
