<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Avisos del Departamento de Sistemas') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <div class="bg-[#fdf2f6] border-l-4 border-[#621132] text-gray-700 p-4 text-sm">
                Aquí aparecen los avisos que te envía el Departamento de Sistemas, por ejemplo cuando se te asignan
                credenciales o cuando cambia una contraseña. Revisa este apartado con frecuencia. Algunos avisos piden
                que confirmes con el botón <strong>"Estoy enterado"</strong>.
            </div>

            {{-- Escribir a Sistemas --}}
            <div class="bg-white shadow rounded-lg p-6 border-t-4 border-[#621132]">
                <h3 class="text-lg font-bold text-[#621132] mb-3">Escribir al Departamento de Sistemas</h3>
                <form action="{{ route('mensajes.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Asunto</label>
                        <input type="text" name="asunto" value="{{ old('asunto') }}" maxlength="150" required
                               class="w-full rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Mensaje</label>
                        <textarea name="cuerpo" rows="4" maxlength="2000" required
                                  class="w-full rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] text-sm">{{ old('cuerpo') }}</textarea>
                    </div>
                    <button type="submit" class="bg-[#621132] hover:bg-[#4d0d27] text-white font-bold py-2 px-6 rounded shadow text-sm">
                        Enviar mensaje
                    </button>
                </form>
            </div>

            {{-- Historial --}}
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-gray-800">Historial</h3>

                @forelse($mensajes as $m)
                    <div class="bg-white shadow rounded-lg p-5 border-l-4 {{ $m->de_admin ? 'border-[#621132]' : 'border-gray-300' }}">
                        <div class="flex justify-between items-start gap-3">
                            <div>
                                <p class="text-xs text-gray-500">
                                    {{ $m->de_admin ? 'De: Departamento de Sistemas' : 'Enviado por ti a Sistemas' }}
                                    &middot; {{ $m->created_at->format('d/m/Y H:i') }}
                                </p>
                                <p class="font-bold text-gray-800 mt-1">{{ $m->asunto }}</p>
                            </div>
                            @if(in_array($m->id, $idsNuevos))
                                <span class="text-xs font-bold text-white bg-[#621132] rounded-full px-2 py-0.5">Nuevo</span>
                            @endif
                        </div>

                        <p class="text-sm text-gray-700 mt-3">{!! nl2br(e($m->cuerpo)) !!}</p>

                        @if($m->de_admin && $m->requiere_confirmacion)
                            <div class="mt-4 pt-3 border-t">
                                @if($m->confirmado_at)
                                    <p class="text-sm text-green-700 font-bold">
                                        Confirmado el {{ $m->confirmado_at->format('d/m/Y H:i') }}
                                    </p>
                                @else
                                    <p class="text-sm text-red-700 font-bold mb-2">Este aviso requiere tu confirmación.</p>
                                    <form action="{{ route('mensajes.confirmar', $m->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="bg-[#621132] hover:bg-[#4d0d27] text-white font-bold py-2 px-5 rounded shadow text-sm">
                                            Estoy enterado
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="bg-white shadow rounded-lg p-6 text-sm text-gray-500">
                        Todavía no tienes avisos.
                    </div>
                @endforelse

                <div>{{ $mensajes->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
