<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $usuario->name ?? 'Usuario eliminado' }}
                </h2>
                @if($usuario)
                    <p class="text-xs text-gray-500">Matrícula {{ $usuario->numero_empleado }}</p>
                @endif
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.mensajes.index') }}" class="text-sm text-gray-600 underline">&larr; Todas las conversaciones</a>
                <form action="{{ route('admin.mensajes.destroyConversacion', $id) }}" method="POST"
                      onsubmit="return confirm('¿Eliminar toda esta conversación? Esta acción no se puede deshacer.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-600 underline">Eliminar conversación</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Pestañas --}}
            <div class="flex flex-wrap gap-2 text-sm">
                @if($usuario)
                    <a href="{{ route('admin.mensajes.show', ['id' => $id, 'tipo' => 'nuevo']) }}"
                       class="px-4 py-2 rounded-full border font-bold {{ $tipo === 'nuevo' ? 'bg-[#621132] text-white border-[#621132]' : 'text-gray-700 border-gray-300 hover:bg-gray-50' }}">
                        Nuevo mensaje
                    </a>
                @endif
                <a href="{{ route('admin.mensajes.show', ['id' => $id, 'tipo' => 'avisos']) }}"
                   class="px-4 py-2 rounded-full border font-bold {{ $tipo === 'avisos' ? 'bg-[#621132] text-white border-[#621132]' : 'text-gray-700 border-gray-300 hover:bg-gray-50' }}">
                    Avisos enviados ({{ $conteos['avisos'] }})
                </a>
                <a href="{{ route('admin.mensajes.show', ['id' => $id, 'tipo' => 'usuario']) }}"
                   class="px-4 py-2 rounded-full border font-bold {{ $tipo === 'usuario' ? 'bg-[#621132] text-white border-[#621132]' : 'text-gray-700 border-gray-300 hover:bg-gray-50' }}">
                    Mensajes del empleado ({{ $conteos['usuario'] }})
                    @if($conteos['sin_leer'] > 0)
                        <span class="ml-1 text-xs font-bold text-white bg-red-600 rounded-full px-2 py-0.5">{{ $conteos['sin_leer'] }} sin leer</span>
                    @endif
                </a>
            </div>

            {{-- NUEVO MENSAJE --}}
            @if($tipo === 'nuevo')
                @if($usuario)
                    <div class="bg-white shadow rounded-lg p-6 border-t-4 border-[#621132]">
                        <h3 class="font-bold text-[#621132] mb-4 text-lg">Escribir a {{ $usuario->nombres }}</h3>

                        <form action="{{ route('admin.mensajes.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="usuario_id" value="{{ $usuario->id }}">

                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Asunto</label>
                                <input type="text" name="asunto" value="{{ old('asunto', request('asunto')) }}" maxlength="150" required
                                       class="w-full rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] text-sm">
                            </div>

                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Mensaje</label>
                                <textarea name="cuerpo" rows="8" maxlength="2000" required
                                          class="w-full rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] text-sm">{{ old('cuerpo') }}</textarea>
                                <p class="text-xs text-gray-500 mt-1">El texto se guarda cifrado en la base de datos. El asunto no se cifra: evita poner datos sensibles en él.</p>
                            </div>

                            <label class="flex items-start gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="requiere_confirmacion" value="1"
                                       {{ old('requiere_confirmacion') ? 'checked' : '' }}
                                       class="mt-1 rounded border-gray-300 text-[#621132] focus:ring-[#621132]">
                                <span><strong>Requiere confirmación.</strong> La persona verá el botón "Estoy enterado" y quedará registrado cuándo lo presionó.</span>
                            </label>

                            <button type="submit" class="bg-[#621132] hover:bg-[#4d0d27] text-white font-bold py-2 px-8 rounded shadow text-sm">
                                Enviar aviso
                            </button>
                        </form>
                    </div>
                @else
                    <div class="bg-white shadow rounded-lg p-6 text-sm text-gray-500">
                        Esta cuenta ya no existe, por lo que no se pueden enviar nuevos avisos.
                    </div>
                @endif

            {{-- AVISOS ENVIADOS / MENSAJES DEL EMPLEADO --}}
            @else
                <div class="space-y-3">
                    @forelse($mensajes as $m)
                        @php
                            $esNuevo = in_array($m->id, $idsNuevos);
                            if ($m->de_admin) {
                                if ($m->requiere_confirmacion) {
                                    $estado = $m->confirmado_at
                                        ? 'Confirmado el ' . \Carbon\Carbon::parse($m->confirmado_at)->format('d/m/Y H:i')
                                        : ($m->leido_at ? 'Leído, falta confirmar' : 'Pendiente de confirmar');
                                } else {
                                    $estado = $m->leido_at ? 'Leído' : 'Sin leer';
                                }
                                $pendiente = $m->requiere_confirmacion && !$m->confirmado_at;
                            }
                        @endphp

                        <div class="bg-white shadow rounded-lg p-5 border-l-4 {{ $m->de_admin ? 'border-[#621132]' : 'border-gray-400' }} {{ $esNuevo ? 'bg-[#fdf2f6]' : '' }}">
                            <div class="flex justify-between items-start gap-4">
                                <div class="min-w-0">
                                    <p class="text-xs text-gray-500">
                                        {{ $m->de_admin ? 'Enviado por Sistemas' : 'Mensaje de ' . ($usuario->nombres ?? 'el empleado') }}
                                        &middot; {{ $m->created_at->format('d/m/Y H:i') }}
                                    </p>
                                    <p class="font-bold text-gray-800 mt-1">{{ $m->asunto }}</p>
                                </div>

                                <div class="flex-shrink-0 text-right">
                                    @if($m->de_admin)
                                        <span class="text-xs font-bold rounded-full px-3 py-1 {{ $pendiente ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $estado }}
                                        </span>
                                    @elseif($esNuevo)
                                        <span class="text-xs font-bold text-white bg-[#621132] rounded-full px-3 py-1">Nuevo</span>
                                    @endif
                                </div>
                            </div>

                            <p class="text-sm text-gray-700 mt-3">{!! nl2br(e($m->cuerpo)) !!}</p>

                            <form action="{{ route('admin.mensajes.destroy', $m->id) }}" method="POST" class="mt-2"
                                  onsubmit="return confirm('¿Eliminar este mensaje?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-600 underline">Eliminar mensaje</button>
                            </form>

                            @if(!$m->de_admin && $usuario)
                                <div class="mt-3">
                                    <a href="{{ route('admin.mensajes.show', ['id' => $id, 'tipo' => 'nuevo', 'asunto' => 'Re: ' . $m->asunto]) }}"
                                       class="text-sm font-bold text-[#621132] underline">Responder</a>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="bg-white shadow rounded-lg p-6 text-sm text-gray-500">
                            {{ $tipo === 'avisos' ? 'Todavía no se han enviado avisos a esta persona.' : 'Esta persona no ha enviado mensajes.' }}
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
