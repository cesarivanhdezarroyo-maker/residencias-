<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Datos del Excel Oficial') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg p-6 border-t-4 border-[#621132]">
                <h3 class="text-lg font-bold text-[#621132] mb-1">Nombres que aparecen en el Excel</h3>
                <p class="text-xs text-gray-500 mb-6">
                    Son los tres nombres que cambian cuando cambia la persona en el puesto -- el resto del formato oficial no se toca desde aquí.
                </p>

                <form action="{{ route('admin.configuracion.excel.actualizar') }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PATCH')

                    <div class="border-b pb-4">
                        <p class="text-sm font-bold text-gray-700 mb-2">Nombre de quien autoriza</p>

                        <label class="block text-xs text-gray-600 mb-1">Nombre completo</label>
                        <input type="text" name="autoriza_nombre" value="{{ old('autoriza_nombre', $configuracion->autoriza_nombre) }}"
                               class="w-full rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] text-sm" required>
                        @error('autoriza_nombre') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror

                        <label class="block text-xs text-gray-600 mt-3 mb-1">Cargo</label>
                        <input type="text" name="autoriza_cargo" value="{{ old('autoriza_cargo', $configuracion->autoriza_cargo) }}"
                               class="w-full rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] text-sm" required>
                        @error('autoriza_cargo') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="border-b pb-4">
                        <p class="text-sm font-bold text-gray-700 mb-2">Enlace Informático</p>

                        <label class="block text-xs text-gray-600 mb-1">Nombre completo</label>
                        <input type="text" name="enlace_nombre" value="{{ old('enlace_nombre', $configuracion->enlace_nombre) }}"
                               class="w-full rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] text-sm" required>
                        @error('enlace_nombre') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <p class="text-sm font-bold text-gray-700 mb-2">Jefe de Servicios o Superior</p>

                        <label class="block text-xs text-gray-600 mb-1">Nombre completo</label>
                        <input type="text" name="jefe_servicios_nombre" value="{{ old('jefe_servicios_nombre', $configuracion->jefe_servicios_nombre) }}"
                               class="w-full rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] text-sm" required>
                        @error('jefe_servicios_nombre') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="bg-[#621132] hover:bg-[#4d0d27] text-white font-bold py-2 px-6 rounded shadow text-sm">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
