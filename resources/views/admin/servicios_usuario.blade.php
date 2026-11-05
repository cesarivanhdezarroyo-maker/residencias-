<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Servicios registrados</h2>
                <p class="text-xs text-gray-500">{{ $usuario->name }} · Matrícula {{ $usuario->numero_empleado }}</p>
            </div>
            <a href="{{ route('admin.solicitudes.usuario', $usuario->id) }}" class="text-sm text-gray-600 underline">&larr; Volver al expediente</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <p class="text-sm text-gray-600">
                Servicios que la persona tiene activos: los que <strong>declaró ella misma</strong> (sin verificar) y los que
                se <strong>aprobaron desde una solicitud</strong>. Es una vista de consulta.
            </p>

            <div class="bg-white shadow rounded-lg p-6 border-t-4 border-[#621132]">
                <h3 class="text-lg font-bold text-[#621132] mb-4">Servicios activos</h3>

                @if($activos->isEmpty())
                    <p class="text-sm text-gray-500">Esta persona no tiene servicios registrados.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs text-gray-500 border-b">
                                    <th class="py-2 pr-3">Servicio</th>
                                    <th class="pr-3">Categoría</th>
                                    <th class="pr-3">Cuenta o equipo</th>
                                    <th class="pr-3">Origen</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activos as $s)
                                    <tr class="border-b align-top">
                                        <td class="py-2 pr-3">{{ $s->servicio }}</td>
                                        <td class="pr-3">{{ $s->categoria }}</td>
                                        <td class="pr-3">
                                            {{ $s->identificador ?? '—' }}
                                            @if($s->notas)
                                                <div class="text-xs text-gray-500 italic">{{ $s->notas }}</div>
                                            @endif
                                        </td>
                                        <td class="pr-3">
                                            @if($s->origen === 'solicitud')
                                                <span class="text-xs font-bold bg-green-100 text-green-800 rounded-full px-2 py-0.5">Aprobado por solicitud</span>
                                            @else
                                                <span class="text-xs font-bold bg-amber-100 text-amber-800 rounded-full px-2 py-0.5">Declarado por la persona</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap">{{ $s->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            @if($bajas->isNotEmpty())
                <div class="bg-white shadow rounded-lg p-6 border-t-4 border-gray-400">
                    <h3 class="text-lg font-bold text-gray-700 mb-3">Dados de baja</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        @foreach($bajas as $s)
                            <li>{{ $s->servicio }} — {{ $s->categoria }}
                                @if($s->identificador) ({{ $s->identificador }}) @endif
                                <span class="text-xs text-gray-400">· {{ $s->updated_at->format('d/m/Y') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
