<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Administración TI - ISSSTE') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                    <p class="font-bold">¡Actualización Exitosa!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-[#621132]">
                <div class="p-6 text-gray-900">
                    
                    <h3 class="text-2xl font-bold text-[#621132] mb-6">Bandeja General de Solicitudes</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                <tr>
                                    <th class="py-3 px-4 text-left">Fecha</th>
                                    <th class="py-3 px-4 text-left">Usuario (Matrícula)</th>
                                    <th class="py-3 px-4 text-left">Trámite Solicitado</th>
                                    <th class="py-3 px-4 text-center">Evidencia</th>
                                    <th class="py-3 px-4 text-center">Formato Excel</th>
                                    <th class="py-3 px-4 text-center">Resolución de TI</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 text-sm font-light">
                                @forelse ($solicitudes as $solicitud)
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <!-- Fecha -->
                                        <td class="py-3 px-4 text-left whitespace-nowrap">
                                            <span class="font-bold">{{ $solicitud->created_at->format('d/m/Y') }}</span>
                                        </td>
                                        
                                        <!-- Datos del Usuario -->
                                        <td class="py-3 px-4 text-left">
                                            <div class="font-bold text-gray-800">{{ $solicitud->usuario->nombres ?? 'Usuario' }} {{ $solicitud->usuario->apellidos ?? '' }}</div>
                                            <div class="text-xs text-gray-500">Matrícula: {{ $solicitud->usuario->numero_empleado ?? 'N/A' }}</div>
                                        </td>
                                        
                                        <!-- Servicio -->
                                        <td class="py-3 px-4 text-left">
                                            <span class="font-bold text-[#621132]">{{ $solicitud->tipo_solicitud }}</span> - 
                                            {{ $solicitud->servicio->nombre_servicio ?? 'Servicio' }}
                                            <div class="text-xs text-gray-500 italic">{{ $solicitud->categoria->nombre_categoria ?? '' }}</div>
                                        </td>
                                        
                                        <!-- Imagen de Evidencia -->
                                        <td class="py-3 px-4 text-center">
                                            @if($solicitud->imagen_evidencia_path && $solicitud->imagen_evidencia_path !== 'pendiente.jpg')
                                                <a href="{{ asset('storage/' . $solicitud->imagen_evidencia_path) }}" target="_blank" class="text-blue-500 hover:text-blue-700 underline font-semibold flex items-center justify-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                    Ver Captura
                                                </a>
                                            @else
                                                <span class="text-gray-400 text-xs">Sin imagen</span>
                                            @endif
                                        </td>
                                        
                                        <!-- Descarga Excel -->
                                        <td class="py-3 px-4 text-center">
                                            @if($solicitud->pdf_generado_path)
                                                <a href="{{ asset('storage/' . $solicitud->pdf_generado_path) }}" class="text-green-600 hover:text-green-800 font-bold underline">
                                                    Descargar
                                                </a>
                                            @else
                                                <span class="text-red-400">Error</span>
                                            @endif
                                        </td>

                                        <!-- Selector de Estado (Resolución) -->
                                        <td class="py-3 px-4 text-center">
                                            <form action="{{ route('admin.solicitudes.estado', $solicitud->id) }}" method="POST" class="flex flex-col items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <select name="estado_solicitud" class="text-xs rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring focus:ring-[#621132] focus:ring-opacity-50">
                                                    <option value="Pendiente" {{ $solicitud->estado_solicitud === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                                    <option value="Aprobado" {{ $solicitud->estado_solicitud === 'Aprobado' ? 'selected' : '' }}>Aprobado</option>
                                                    <option value="Rechazado" {{ $solicitud->estado_solicitud === 'Rechazado' ? 'selected' : '' }}>Rechazado</option>
                                                </select>
                                                <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white text-xs font-bold py-1 px-2 rounded">
                                                    Guardar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 px-6 text-center text-gray-500 font-bold text-lg">
                                            Bandeja limpia. No hay solicitudes pendientes.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>