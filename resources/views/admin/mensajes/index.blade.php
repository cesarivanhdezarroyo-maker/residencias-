<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Mensajes y avisos') }}
            </h2>
            <a href="{{ route('admin.mensajes.create') }}"
               class="bg-[#621132] hover:bg-[#4d0d27] text-white font-bold py-2 px-5 rounded shadow text-sm">
                Nuevo aviso
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Filtros y búsqueda --}}
            <div class="bg-white shadow rounded-lg p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div class="flex flex-wrap gap-2 text-sm">
                    <a href="{{ route('admin.mensajes.index') }}"
                       class="px-3 py-1 rounded-full border {{ !$filtro ? 'bg-[#621132] text-white border-[#621132]' : 'text-gray-700 border-gray-300 hover:bg-gray-50' }}">
                        Todas
                    </a>
                    <a href="{{ route('admin.mensajes.index', ['filtro' => 'sin_leer']) }}"
                       class="px-3 py-1 rounded-full border {{ $filtro === 'sin_leer' ? 'bg-[#621132] text-white border-[#621132]' : 'text-gray-700 border-gray-300 hover:bg-gray-50' }}">
                        Con mensajes sin leer ({{ $totalSinLeer }})
                    </a>
                    <a href="{{ route('admin.mensajes.index', ['filtro' => 'pendientes']) }}"
                       class="px-3 py-1 rounded-full border {{ $filtro === 'pendientes' ? 'bg-[#621132] text-white border-[#621132]' : 'text-gray-700 border-gray-300 hover:bg-gray-50' }}">
                        Con confirmaciones pendientes ({{ $totalPorConfirmar }})
                    </a>
                </div>

                <form method="GET" action="{{ route('admin.mensajes.index') }}" class="flex gap-2">
                    @if($filtro)
                        <input type="hidden" name="filtro" value="{{ $filtro }}">
                    @endif
                    <input type="text" name="q" value="{{ $busqueda }}" placeholder="Buscar por nombre o matrícula"
                           class="rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] text-sm w-56">
                    <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold px-3 rounded">Buscar</button>
                </form>
            </div>

            {{-- Conversaciones --}}
            <div class="bg-white shadow rounded-lg divide-y border-t-4 border-[#621132]">
                @forelse($conversaciones as $c)
                    @php
                        $nombre = $c->usuario->name ?? 'Usuario eliminado';
                        $inicial = mb_strtoupper(mb_substr($nombre, 0, 1));
                        $fecha = \Carbon\Carbon::parse($c->ultimo)->format('d/m/Y H:i');
                        $ultimo = $c->ultimo_mensaje;
                    @endphp
                    <a href="{{ route('admin.mensajes.show', $c->usuario_id) }}"
                       class="flex items-center gap-4 p-4 hover:bg-[#fdf2f6] {{ $c->sin_leer > 0 ? 'bg-[#fdf2f6]' : '' }}">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-[#621132] text-white flex items-center justify-center font-bold">
                            {{ $inicial }}
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start gap-3">
                                <p class="font-bold text-gray-800 truncate">
                                    {{ $nombre }}
                                    @if($c->usuario)
                                        <span class="text-xs font-normal text-gray-500">Matrícula {{ $c->usuario->numero_empleado }}</span>
                                    @endif
                                </p>
                                <span class="text-xs text-gray-500 whitespace-nowrap">{{ $fecha }}</span>
                            </div>

                            <p class="text-sm text-gray-600 truncate">
                                @if($ultimo)
                                    {{ $ultimo->de_admin ? 'Sistemas: ' : '' }}{{ $ultimo->asunto }}
                                @endif
                            </p>

                            <div class="mt-1 flex flex-wrap gap-2">
                                @if($c->sin_leer > 0)
                                    <span class="text-xs font-bold text-white bg-[#621132] rounded-full px-2 py-0.5">
                                        {{ $c->sin_leer }} sin leer
                                    </span>
                                @endif
                                @if($c->por_confirmar > 0)
                                    <span class="text-xs font-bold text-amber-800 bg-amber-100 rounded-full px-2 py-0.5">
                                        {{ $c->por_confirmar }} por confirmar
                                    </span>
                                @endif
                                <span class="text-xs text-gray-400">{{ $c->total }} mensaje(s)</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <p class="p-6 text-sm text-gray-500">
                        @if($filtro || $busqueda)
                            No hay conversaciones que coincidan con el filtro.
                        @else
                            Todavía no hay conversaciones. Usa "Nuevo aviso" para escribirle a un empleado.
                        @endif
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
