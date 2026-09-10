<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Limpieza de Archivos
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-300 text-green-800 text-sm rounded p-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-gray-600 mb-1">
                    Esto borra del servidor el <strong>Excel oficial</strong> y la <strong>imagen de evidencia</strong>
                    de las solicitudes ya <strong>Aprobadas o Rechazadas</strong> que tengan más antigüedad de la que
                    elijas abajo.
                </p>
                <p class="text-xs text-gray-500 mb-6">
                    El registro de la solicitud (fecha, servicio, estado, credenciales) <strong>no se borra</strong> --
                    solo se libera el espacio en disco de esos dos archivos.
                </p>

                <form action="{{ route('admin.limpieza.ejecutar') }}" method="POST"
                      onsubmit="return confirm('Esto va a borrar de forma permanente los archivos que cumplan el periodo indicado. ¿Continuar?');">
                    @csrf

                    <label class="block text-sm font-bold text-gray-700 mb-1">
                        Borrar archivos de solicitudes con más de:
                    </label>
                    <div class="flex items-center gap-2 mb-1">
                        <select name="meses" class="rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132]">
                            <option value="0">0 meses (todo lo ya resuelto, sin importar la fecha)</option>
                            <option value="1">1 mes</option>
                            <option value="2" selected>2 meses</option>
                            <option value="3">3 meses</option>
                            <option value="6">6 meses</option>
                            <option value="12">12 meses</option>
                        </select>
                    </div>
                    @error('meses')
                        <p class="text-red-600 text-xs font-semibold mb-2">{{ $message }}</p>
                    @enderror

                    <button type="submit"
                            class="mt-4 bg-red-700 hover:bg-red-800 text-white font-bold py-2 px-4 rounded shadow text-sm">
                        Borrar archivos ahora
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
