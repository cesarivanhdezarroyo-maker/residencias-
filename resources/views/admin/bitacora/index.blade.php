<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Bitácora de cambios') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <p class="text-sm text-gray-600">
                Aquí queda registrado lo que el área de Sistemas cambia en la plataforma: estados de solicitudes, eliminaciones,
                credenciales, mensajes, limpieza y datos del oficio. <strong>No se guardan contraseñas ni el texto de los mensajes.</strong>
                Es de solo consulta: este registro no se puede editar ni borrar desde el sistema.
            </p>

            {{-- Filtros --}}
            <form method="GET" action="{{ route('admin.bitacora.index') }}" class="bg-white shadow rounded-lg p-4 grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Desde</label>
                    <input type="date" name="desde" value="{{ request('desde') }}" class="w-full rounded border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Hasta</label>
                    <input type="date" name="hasta" value="{{ request('hasta') }}" class="w-full rounded border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Tipo de acción</label>
                    <select name="accion" class="w-full rounded border-gray-300 text-sm">
                        <option value="">Todas</option>
                        @foreach($acciones as $a)
                            <option value="{{ $a }}" {{ request('accion') === $a ? 'selected' : '' }}>{{ $etiquetas[$a] ?? $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Buscar (nombre o matrícula)</label>
                    <input type="text" name="q" value="{{ request('q') }}" maxlength="100" class="w-full rounded border-gray-300 text-sm">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-[#621132] hover:bg-[#4d0d27] text-white font-bold py-2 px-4 rounded text-sm">Filtrar</button>
                    <a href="{{ route('admin.bitacora.index') }}" class="py-2 px-3 text-sm text-gray-600 underline">Limpiar</a>
                </div>
            </form>

            <div class="bg-white shadow rounded-lg p-4">
                @if($registros->isEmpty())
                    <p class="text-sm text-gray-500">No hay registros con esos filtros.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs text-gray-500 border-b">
                                    <th class="py-2 pr-3">Fecha y hora</th>
                                    <th class="pr-3">Quién lo hizo</th>
                                    <th class="pr-3">Qué se hizo</th>
                                    <th class="pr-3">IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($registros as $r)
                                    <tr class="border-b align-top">
                                        <td class="py-2 pr-3 whitespace-nowrap">{{ $r->created_at?->format('d/m/Y H:i:s') }}</td>
                                        <td class="pr-3">
                                            {{ $r->actor_nombre ?? '—' }}
                                            @if($r->actor_matricula)
                                                <span class="block text-xs text-gray-500">{{ $r->actor_matricula }}</span>
                                            @endif
                                        </td>
                                        <td class="pr-3">
                                            {{ $r->descripcion }}
                                            <span class="block text-xs text-gray-400">{{ $etiquetas[$r->accion] ?? $r->accion }}</span>
                                        </td>
                                        <td class="pr-3 whitespace-nowrap text-xs text-gray-500">{{ $r->ip }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $registros->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
