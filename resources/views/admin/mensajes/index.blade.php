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
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Pendientes de confirmación --}}
            <div class="bg-white shadow rounded-lg p-6 border-t-4 border-red-600">
                <h3 class="text-lg font-bold text-red-700 mb-1">Pendientes de confirmación</h3>
                <p class="text-xs text-gray-500 mb-4">
                    Avisos que requieren que la persona presione "Estoy enterado". Revisa esta lista antes de hacer un cambio
                    (por ejemplo, de contraseña).
                </p>

                @if($pendientes->isEmpty())
                    <p class="text-sm text-gray-500">No hay avisos pendientes de confirmar.</p>
                @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 border-b">
                                <th class="py-2">Empleado</th>
                                <th>Asunto</th>
                                <th>Enviado</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendientes as $p)
                                <tr class="border-b">
                                    <td class="py-2">{{ $p->usuario->name ?? 'Usuario eliminado' }}</td>
                                    <td>{{ $p->asunto }}</td>
                                    <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $p->leido_at ? 'Leído, falta confirmar' : 'Sin leer' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            {{-- Mensajes de empleados --}}
            <div class="bg-white shadow rounded-lg p-6 border-t-4 border-[#621132]">
                <h3 class="text-lg font-bold text-[#621132] mb-4">Mensajes de empleados</h3>

                @forelse($recibidos as $m)
                    <div class="border rounded p-4 mb-3 {{ $m->leido_at ? 'bg-white' : 'bg-[#fdf2f6]' }}">
                        <div class="flex justify-between items-start gap-3">
                            <div>
                                <p class="text-xs text-gray-500">
                                    {{ $m->usuario->name ?? 'Usuario eliminado' }}
                                    @if($m->usuario) &middot; Matrícula {{ $m->usuario->numero_empleado }} @endif
                                    &middot; {{ $m->created_at->format('d/m/Y H:i') }}
                                </p>
                                <p class="font-bold text-gray-800 mt-1">{{ $m->asunto }}</p>
                            </div>
                            @unless($m->leido_at)
                                <span class="text-xs font-bold text-white bg-[#621132] rounded-full px-2 py-0.5">Sin leer</span>
                            @endunless
                        </div>

                        <p class="text-sm text-gray-700 mt-2">{!! nl2br(e($m->cuerpo)) !!}</p>

                        <div class="mt-3 flex gap-3">
                            @if($m->usuario)
                                <a href="{{ route('admin.mensajes.create', ['usuario_id' => $m->usuario_id, 'asunto' => 'Re: ' . $m->asunto]) }}"
                                   class="text-sm font-bold text-[#621132] underline">Responder</a>
                            @endif
                            @unless($m->leido_at)
                                <form action="{{ route('admin.mensajes.leido', $m->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-sm text-gray-600 underline">Marcar como leído</button>
                                </form>
                            @endunless
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Todavía no hay mensajes de empleados.</p>
                @endforelse

                <div>{{ $recibidos->links() }}</div>
            </div>

            {{-- Avisos enviados --}}
            <div class="bg-white shadow rounded-lg p-6 border-t-4 border-gray-400">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Avisos enviados</h3>

                @if($enviados->isEmpty())
                    <p class="text-sm text-gray-500">Todavía no has enviado avisos.</p>
                @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 border-b">
                                <th class="py-2">Empleado</th>
                                <th>Asunto</th>
                                <th>Enviado</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($enviados as $m)
                                @php
                                    if ($m->requiere_confirmacion) {
                                        $estado = $m->confirmado_at
                                            ? 'Confirmado'
                                            : ($m->leido_at ? 'Leído, falta confirmar' : 'Sin leer');
                                    } else {
                                        $estado = $m->leido_at ? 'Leído' : 'Sin leer';
                                    }
                                @endphp
                                <tr class="border-b">
                                    <td class="py-2">{{ $m->usuario->name ?? 'Usuario eliminado' }}</td>
                                    <td>{{ $m->asunto }}</td>
                                    <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $estado }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-3">{{ $enviados->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
