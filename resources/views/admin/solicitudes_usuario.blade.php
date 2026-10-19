<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Trámites del Empleado - Panel TI') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                    <p class="font-bold">¡Éxito!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <!-- Botón de regreso -->
            <a href="{{ route('admin.usuarios.index') }}" class="inline-flex items-center text-sm font-bold text-gray-500 hover:text-[#621132] mb-4">
                ← Regresar a Lista de Usuarios
            </a>

            <div class="mb-6">
                <h3 class="text-2xl font-bold text-gray-800">Expediente de Trámites</h3>
                <p class="text-gray-500 text-sm">Personal: <span class="font-bold text-[#621132]">{{ $usuario->name }}</span> (Matrícula: {{ $usuario->numero_empleado }})</p>
            </div>

            <!-- ===================== CREDENCIALES DE LA PLATAFORMA (independiente de las solicitudes) ===================== -->
            <div x-data="{ open: false }" class="mb-8">
                <button @click="open = !open" type="button"
                        class="bg-[#621132] hover:bg-[#4d0d27] text-white font-bold py-2 px-4 rounded shadow text-sm">
                    Actualizar credenciales de mi plataforma
                </button>

                <div x-show="open" x-cloak x-transition
                     class="mt-3 bg-white border border-gray-200 rounded-lg p-5 shadow-sm max-w-md">

                    @if($usuario->usuario_sistema)
                        <p class="text-xs text-green-700 font-bold mb-3">
                            ✓ Login activo actualmente: <span class="font-mono">{{ $usuario->usuario_sistema }}</span>
                        </p>
                    @else
                        <p class="text-xs text-gray-500 mb-3">
                            Este empleado todavía entra con las credenciales que usó al registrarse.
                        </p>
                    @endif

                    <form action="{{ route('admin.usuarios.credenciales_plataforma', $usuario->id) }}" method="POST"
                          class="space-y-3"
                          onsubmit="return confirm('Esto va a reemplazar la contraseña con la que {{ $usuario->name }} entra a la plataforma. ¿Continuar?');">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Usuario (6 caracteres)</label>
                            <input type="text" name="credencial_usuario" minlength="6" maxlength="6" required
                                   class="text-sm rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] w-full">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Contraseña (mínimo 8 caracteres)</label>
                            <input type="text" name="credencial_password" minlength="8" required
                                   class="text-sm rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] w-full">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Confirmar contraseña</label>
                            <input type="text" name="credencial_password_confirmation" minlength="8" required
                                   class="text-sm rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] w-full">
                        </div>



                        <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white text-sm font-bold py-2 px-4 rounded w-full">
                            Guardar credenciales
                        </button>
                    </form>
                </div>
            </div>

            <!-- ===================== ALTAS ===================== -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-green-700 mb-8">
                <div class="p-6 text-gray-900">
                    <h4 class="text-lg font-bold text-green-800 mb-4">Altas</h4>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead class="bg-gray-100 text-gray-600 uppercase text-xs leading-normal">
                                <tr>
                                    <th class="py-3 px-4 text-left">Fecha</th>
                                    <th class="py-3 px-4 text-left">Trámite Solicitado</th>
                                    <th class="py-3 px-4 text-center">Evidencia</th>
                                    <th class="py-3 px-4 text-center">Resolución</th>
                                    <th class="py-3 px-4 text-left">Credenciales asignadas (México)</th>
                                    <th class="py-3 px-4 text-center">Borrar</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 text-sm font-light">
                                @forelse ($altas as $solicitud)
                                    <tr class="border-b border-gray-200 hover:bg-gray-50 align-top">
                                        <td class="py-3 px-4 text-left whitespace-nowrap font-bold">{{ $solicitud->created_at->format('d/m/Y') }}</td>
                                        <td class="py-3 px-4 text-left">
                                            <span class="font-bold text-[#621132]">{{ $solicitud->tipo_solicitud }}</span> -
                                            {{ $solicitud->servicio->nombre_servicio ?? 'Servicio' }}
                                            <div class="text-xs text-gray-500 italic">{{ $solicitud->categoria->nombre_categoria ?? '' }}</div>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex flex-col gap-1 items-center">
                                                @if($solicitud->imagen_evidencia_path && $solicitud->imagen_evidencia_path !== 'pendiente.jpg')
                                                    <a href="{{ route('admin.solicitudes.evidencia', $solicitud->id) }}" target="_blank" class="text-blue-500 hover:text-blue-700 underline text-xs font-semibold">Ver documento</a>
                                                @else
                                                    <span class="text-gray-400 text-xs">Sin imagen</span>
                                                @endif

                                                @if($solicitud->pdf_generado_path)
                                                    <a href="{{ route('admin.solicitudes.excel', $solicitud->id) }}" class="text-green-600 hover:text-green-800 underline text-xs font-semibold">Bajar Excel</a>
                                                    <a href="{{ route('admin.solicitudes.pdf', $solicitud->id) }}" class="text-red-600 hover:text-red-800 underline text-xs font-semibold">Generar PDF</a>
                                                @else
                                                    <span class="text-gray-400 text-xs">Archivo eliminado (limpieza)</span>
                                                @endif
                                                @if($solicitud->pdf_generado_path)
                                                    <a href="{{ route('admin.solicitudes.oficio', $solicitud->id) }}" class="text-indigo-600 hover:text-indigo-800 underline text-xs font-semibold">Descargar Oficio</a>
                                                @endif

                                                {{-- NOTA: aquí faltan agregar "Generar PDF" y "Descargar Oficio" para el admin --}}
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <form action="{{ route('admin.solicitudes.estado', $solicitud->id) }}" method="POST" class="flex flex-col items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <select name="estado_solicitud" class="text-xs rounded border-gray-300 shadow-sm focus:border-[#621132] w-full">
                                                    <option value="Pendiente" {{ $solicitud->estado_solicitud === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                                    <option value="Aprobado" {{ $solicitud->estado_solicitud === 'Aprobado' ? 'selected' : '' }}>Aprobado</option>
                                                    <option value="Rechazado" {{ $solicitud->estado_solicitud === 'Rechazado' ? 'selected' : '' }}>Rechazado</option>
                                                </select>

                                                <!-- Credenciales: solo aplica a Altas -->
                                                <div class="w-full text-left space-y-1 border-t pt-2 mt-1">
                                                    <input type="{{ $solicitud->servicio && $solicitud->servicio->nombre_servicio === 'Cuentas de Red y Correo Institucional (ABC)' ? 'email' : 'text' }}"
                                                           name="credencial_usuario" value="{{ old('credencial_usuario', $solicitud->credencial_usuario) }}"
                                                           placeholder="{{ $solicitud->servicio && $solicitud->servicio->nombre_servicio === 'Cuentas de Red y Correo Institucional (ABC)' ? 'Correo institucional (nombre@issste.gob.mx)' : 'Usuario asignado' }}"
                                                           class="text-xs rounded border-gray-300 shadow-sm focus:border-[#621132] w-full">
                                                    <input type="text" name="credencial_password" value="{{ old('credencial_password', $solicitud->credencial_password) }}"
                                                           placeholder="Contraseña asignada"
                                                           class="text-xs rounded border-gray-300 shadow-sm focus:border-[#621132] w-full">
                                                    <textarea name="credencial_notas" rows="2" placeholder="Notas (o motivo si vas a Rechazar)"
                                                              class="text-xs rounded border-gray-300 shadow-sm focus:border-[#621132] w-full">{{ old('credencial_notas', $solicitud->credencial_notas) }}</textarea>
                                                </div>

                                                @error('credencial_usuario')
                                                    <p class="text-red-600 text-xs font-semibold w-full text-left">{{ $message }}</p>
                                                @enderror

                                                <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white text-xs font-bold py-1 px-3 rounded w-full">Guardar</button>
                                            </form>
                                        </td>
                                        <td class="py-3 px-4 text-left text-xs">
                                            @if($solicitud->credencial_usuario || $solicitud->credencial_password || $solicitud->credencial_notas)
                                                <div class="bg-gray-50 border border-gray-200 rounded p-2 space-y-1">
                                                    @if($solicitud->credencial_usuario)
                                                        <div><span class="font-bold">Usuario:</span> {{ $solicitud->credencial_usuario }}</div>
                                                    @endif
                                                    @if($solicitud->credencial_password)
                                                        <div><span class="font-bold">Contraseña:</span> {{ $solicitud->credencial_password }}</div>
                                                    @endif
                                                    @if($solicitud->credencial_notas)
                                                        <div class="text-gray-500 italic">{{ $solicitud->credencial_notas }}</div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-400">Sin capturar</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <form action="{{ route('admin.solicitudes.eliminar', $solicitud->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta solicitud permanentemente?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 font-bold p-1">
                                                    Borrar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="py-8 text-center text-gray-500">Este usuario no tiene altas registradas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ===================== BAJAS ===================== -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-red-700">
                <div class="p-6 text-gray-900">
                    <h4 class="text-lg font-bold text-red-800 mb-4">Bajas</h4>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead class="bg-gray-100 text-gray-600 uppercase text-xs leading-normal">
                                <tr>
                                    <th class="py-3 px-4 text-left">Fecha</th>
                                    <th class="py-3 px-4 text-left">Trámite Solicitado</th>
                                    <th class="py-3 px-4 text-center">Evidencia</th>
                                    <th class="py-3 px-4 text-center">Resolución</th>
                                    <th class="py-3 px-4 text-left">Acceso invalidado / Motivo</th>
                                    <th class="py-3 px-4 text-center">Borrar</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 text-sm font-light">
                                @forelse ($bajas as $solicitud)
                                    <tr class="border-b border-gray-200 hover:bg-gray-50 align-top">
                                        <td class="py-3 px-4 text-left whitespace-nowrap font-bold">{{ $solicitud->created_at->format('d/m/Y') }}</td>
                                        <td class="py-3 px-4 text-left">
                                            <span class="font-bold text-[#621132]">{{ $solicitud->tipo_solicitud }}</span> -
                                            {{ $solicitud->servicio->nombre_servicio ?? 'Servicio' }}
                                            <div class="text-xs text-gray-500 italic">{{ $solicitud->categoria->nombre_categoria ?? '' }}</div>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex flex-col gap-1 items-center">
                                                @if($solicitud->imagen_evidencia_path && $solicitud->imagen_evidencia_path !== 'pendiente.jpg')
                                                    <a href="{{ route('admin.solicitudes.evidencia', $solicitud->id) }}" target="_blank" class="text-blue-500 hover:text-blue-700 underline text-xs font-semibold">Ver documento</a>
                                                @else
                                                    <span class="text-gray-400 text-xs">Sin imagen</span>
                                                @endif

                                                @if($solicitud->pdf_generado_path)
                                                    <a href="{{ route('admin.solicitudes.excel', $solicitud->id) }}" class="text-green-600 hover:text-green-800 underline text-xs font-semibold">Bajar Excel</a>
                                                    <a href="{{ route('admin.solicitudes.pdf', $solicitud->id) }}" class="text-red-600 hover:text-red-800 underline text-xs font-semibold">Generar PDF</a>
                                                @else
                                                    <span class="text-gray-400 text-xs">Archivo eliminado (limpieza)</span>
                                                @endif
                                                @if($solicitud->pdf_generado_path)
                                                    <a href="{{ route('admin.solicitudes.oficio', $solicitud->id) }}" class="text-indigo-600 hover:text-indigo-800 underline text-xs font-semibold">Descargar Oficio</a>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <form action="{{ route('admin.solicitudes.estado', $solicitud->id) }}" method="POST" class="flex flex-col items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <select name="estado_solicitud" class="text-xs rounded border-gray-300 shadow-sm focus:border-[#621132] w-full">
                                                    <option value="Pendiente" {{ $solicitud->estado_solicitud === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                                    <option value="Aprobado" {{ $solicitud->estado_solicitud === 'Aprobado' ? 'selected' : '' }}>Aprobado</option>
                                                    <option value="Rechazado" {{ $solicitud->estado_solicitud === 'Rechazado' ? 'selected' : '' }}>Rechazado</option>
                                                </select>

                                                <!-- Al aprobar la baja, aquí se anota cómo quedó invalidado el acceso -->
                                                <div class="w-full text-left space-y-1 border-t pt-2 mt-1">
                                                    <input type="text" name="credencial_usuario" value="{{ old('credencial_usuario', $solicitud->credencial_usuario) }}"
                                                           placeholder="Usuario dado de baja"
                                                           class="text-xs rounded border-gray-300 shadow-sm focus:border-[#621132] w-full">
                                                    <input type="text" name="credencial_password" value="{{ old('credencial_password', $solicitud->credencial_password) }}"
                                                           placeholder="Nueva contraseña (bloquea acceso)"
                                                           class="text-xs rounded border-gray-300 shadow-sm focus:border-[#621132] w-full">
                                                    <textarea name="credencial_notas" rows="2" placeholder="Motivo de la baja"
                                                              class="text-xs rounded border-gray-300 shadow-sm focus:border-[#621132] w-full">{{ old('credencial_notas', $solicitud->credencial_notas) }}</textarea>
                                                </div>

                                                <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white text-xs font-bold py-1 px-3 rounded w-full">Guardar</button>
                                            </form>
                                        </td>
                                        <td class="py-3 px-4 text-left text-xs">
                                            @if($solicitud->credencial_usuario || $solicitud->credencial_password || $solicitud->credencial_notas)
                                                <div class="bg-gray-50 border border-gray-200 rounded p-2 space-y-1">
                                                    @if($solicitud->credencial_usuario)
                                                        <div><span class="font-bold">Usuario:</span> {{ $solicitud->credencial_usuario }}</div>
                                                    @endif
                                                    @if($solicitud->credencial_password)
                                                        <div><span class="font-bold">Nueva contraseña:</span> {{ $solicitud->credencial_password }}</div>
                                                    @endif
                                                    @if($solicitud->credencial_notas)
                                                        <div class="text-gray-500 italic">Motivo: {{ $solicitud->credencial_notas }}</div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-400">Sin capturar</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <form action="{{ route('admin.solicitudes.eliminar', $solicitud->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta solicitud permanentemente?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 font-bold p-1">
                                                    Borrar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="py-8 text-center text-gray-500">Este usuario no tiene bajas registradas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
