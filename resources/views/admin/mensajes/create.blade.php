<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nuevo aviso para un empleado') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            @if($errors->any())
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="bg-white shadow rounded-lg p-6 border-t-4 border-[#621132]">
                <form action="{{ route('admin.mensajes.store') }}" method="POST" class="space-y-5">
                    @csrf

                    @if($solicitudId)
                        <input type="hidden" name="solicitud_id" value="{{ $solicitudId }}">
                    @endif

                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Empleado</label>
                        <select name="usuario_id" required
                                class="w-full rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] text-sm">
                            <option value="">Selecciona a la persona</option>
                            @foreach($usuarios as $u)
                                <option value="{{ $u->id }}"
                                    {{ (string) old('usuario_id', $usuarioSeleccionado) === (string) $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} (Matrícula {{ $u->numero_empleado }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Asunto</label>
                        <input type="text" name="asunto" value="{{ old('asunto', $asuntoSugerido) }}" maxlength="150" required
                               class="w-full rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] text-sm">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Mensaje</label>
                        <textarea name="cuerpo" rows="6" maxlength="2000" required
                                  class="w-full rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] text-sm">{{ old('cuerpo') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            El texto se guarda cifrado en la base de datos. La copia por correo lleva el mismo texto.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-start gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="requiere_confirmacion" value="1"
                                   {{ old('requiere_confirmacion') ? 'checked' : '' }}
                                   class="mt-1 rounded border-gray-300 text-[#621132] focus:ring-[#621132]">
                            <span>
                                <strong>Requiere confirmación.</strong>
                                La persona verá el botón "Estoy enterado" y quedará registrado cuándo lo presionó.
                                Úsalo, por ejemplo, antes de cambiar una contraseña.
                            </span>
                        </label>

                        <label class="flex items-start gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="enviar_correo" value="1"
                                   {{ old('enviar_correo', false) ? 'checked' : '' }}
                                   class="mt-1 rounded border-gray-300 text-[#621132] focus:ring-[#621132]">
                            <span>
                                <strong>Enviar también una copia por correo.</strong>
                                Si el correo falla, el aviso se conserva dentro del sistema.
                            </span>
                        </label>
                    </div>

                    <div class="pt-2 flex gap-4 items-center">
                        <button type="submit" class="bg-[#621132] hover:bg-[#4d0d27] text-white font-bold py-2 px-6 rounded shadow text-sm">
                            Enviar aviso
                        </button>
                        <a href="{{ route('admin.mensajes.index') }}" class="text-sm text-gray-600 underline">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
