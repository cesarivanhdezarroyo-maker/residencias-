<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mis Solicitudes - ISSSTE') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                    <p class="font-bold">¡Listo!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                    <p class="font-bold">Atención</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-[#621132]">
                <div class="p-6 text-gray-900">

                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-[#621132]">Historial de Trámites</h3>

                        <a href="{{ route('solicitudes.create') }}" class="bg-[#621132] hover:bg-[#4d0d27] text-white font-bold py-2 px-4 rounded shadow">
                            + Nueva Solicitud
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-700">Fecha</th>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-700">Tipo</th>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-700">Servicio</th>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-700">Estado</th>
                                    <th class="py-2 px-4 border-b text-center text-sm font-semibold text-gray-700">Archivo Excel</th>
                                    <th class="py-2 px-4 border-b text-center text-sm font-semibold text-gray-700">Formato PDF</th>
                                    <th class="py-2 px-4 border-b text-center text-sm font-semibold text-gray-700">Oficio Autorizado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($solicitudes as $solicitud)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-4 border-b text-sm text-gray-800">{{ $solicitud->created_at->format('d/m/Y') }}</td>
                                        <td class="py-2 px-4 border-b text-sm font-bold">{{ $solicitud->tipo_solicitud }}</td>
                                        <td class="py-2 px-4 border-b text-sm text-gray-800">{{ $solicitud->servicio->nombre_servicio ?? 'Servicio' }}</td>
                                        <td class="py-2 px-4 border-b text-sm">
                                            @if($solicitud->estado_solicitud === 'Aprobado')
                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">
                                                    {{ $solicitud->estado_solicitud }}
                                                </span>
                                            @elseif($solicitud->estado_solicitud === 'Rechazado')
                                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold">
                                                    {{ $solicitud->estado_solicitud }}
                                                </span>
                                            @else
                                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold">
                                                    {{ $solicitud->estado_solicitud }}
                                                </span>
                                            @endif

                                            @if($solicitud->estado_solicitud === 'Rechazado' && $solicitud->credencial_notas)
                                                <details class="mt-1">
                                                    <summary class="text-xs text-red-700 underline cursor-pointer font-semibold list-none">
                                                        Ver motivo
                                                    </summary>
                                                    <p class="mt-1 text-xs text-gray-700 bg-red-50 border border-red-200 rounded p-2 max-w-xs">
                                                        {{ $solicitud->credencial_notas }}
                                                    </p>
                                                </details>
                                            @endif

                                            @if($solicitud->estado_solicitud === 'Rechazado')
                                                <a href="{{ route('solicitudes.edit', $solicitud->id) }}" class="block mt-1 text-xs text-blue-700 underline font-semibold">
                                                    Corregir y reenviar
                                                </a>
                                            @endif

                                        </td>
                                        <td class="py-2 px-4 border-b text-center">
                                            @if($solicitud->pdf_generado_path)
                                                <a href="{{ route('solicitudes.descargar', $solicitud->id) }}" class="text-green-600 hover:text-green-800 font-bold underline text-sm">
                                                    Descargar Excel
                                                </a>
                                            @else
                                                <span class="text-gray-400 text-xs italic">Archivo eliminado<br>(limpieza automática)</span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 border-b text-center">
                                            @if($solicitud->pdf_generado_path)
                                                <a href="{{ route('solicitudes.pdf', $solicitud->id) }}" class="inline-block bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded shadow text-xs transition duration-150">
                                                    Generar PDF
                                                </a>
                                            @else
                                                <span class="text-gray-400 text-xs italic">No disponible</span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 border-b text-center">
                                            @if($solicitud->pdf_generado_path)
                                                <a href="{{ route('solicitudes.oficio', $solicitud->id) }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded shadow text-xs transition duration-150">
                                                    Descargar Oficio
                                                </a>
                                            @else
                                                <span class="text-gray-400 text-xs italic">No disponible</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-4 text-center text-gray-500">Aún no tienes solicitudes generadas.</td>
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
