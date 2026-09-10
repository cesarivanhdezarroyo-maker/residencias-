<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Datos del Oficio
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-300 text-green-800 text-sm rounded p-4">
                    {{ session('success') }}
                </div>
            @endif

            <p class="text-sm text-gray-600 mb-6">
                Estos son los dos datos del Oficio que cambian cuando cambia la persona en el puesto --
                el resto del formato no se toca desde aquí.
            </p>

            <form action="{{ route('admin.oficio.actualizar') }}" method="POST" class="space-y-8">
                @csrf
                @method('PATCH')

                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="font-bold text-[#621132] mb-4">Destinatario (AT'N)</h3>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nombre</label>
                            <input type="text" name="atn_nombre" value="{{ old('atn_nombre', $configuracion->atn_nombre) }}"
                                   class="text-sm rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] w-full">
                            @error('atn_nombre') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Puesto</label>
                            <input type="text" name="atn_puesto" value="{{ old('atn_puesto', $configuracion->atn_puesto) }}"
                                   class="text-sm rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] w-full">
                            @error('atn_puesto') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Lugar / Dependencia</label>
                            <input type="text" name="atn_lugar" value="{{ old('atn_lugar', $configuracion->atn_lugar) }}"
                                   class="text-sm rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] w-full">
                            @error('atn_lugar') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="font-bold text-[#621132] mb-4">Firma (Atentamente)</h3>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nombre</label>
                            <input type="text" name="firma_nombre" value="{{ old('firma_nombre', $configuracion->firma_nombre) }}"
                                   class="text-sm rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] w-full">
                            @error('firma_nombre') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Puesto</label>
                            <input type="text" name="firma_puesto" value="{{ old('firma_puesto', $configuracion->firma_puesto) }}"
                                   class="text-sm rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] w-full">
                            @error('firma_puesto') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Lugar</label>
                            <input type="text" name="firma_lugar" value="{{ old('firma_lugar', $configuracion->firma_lugar) }}"
                                   class="text-sm rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] w-full">
                            @error('firma_lugar') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <button type="submit"
                        class="bg-[#621132] hover:bg-[#4d0d27] text-white font-bold py-2 px-6 rounded shadow text-sm">
                    Guardar cambios
                </button>
            </form>

        </div>
    </div>
</x-app-layout>
