<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reportes de Solicitudes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-gray-800">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Exportar solicitudes a Excel</h3>
                    <p class="text-gray-500 text-sm mb-6">
                        Elige un rango de fechas para descargar todas las altas y bajas registradas
                        en ese periodo. La contraseña de las credenciales nunca se incluye en el archivo,
                        por seguridad.
                    </p>

                    <form action="{{ route('admin.reportes.exportar') }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="fecha_inicio" class="block text-sm font-bold text-gray-700 mb-1">Desde</label>
                                <input type="date" id="fecha_inicio" name="fecha_inicio" required
                                       class="block w-full rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132]">
                            </div>
                            <div>
                                <label for="fecha_fin" class="block text-sm font-bold text-gray-700 mb-1">Hasta</label>
                                <input type="date" id="fecha_fin" name="fecha_fin" required
                                       class="block w-full rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132]">
                            </div>
                        </div>

                        <div>
                            <label for="tipo" class="block text-sm font-bold text-gray-700 mb-1">Tipo de trámite</label>
                            <select id="tipo" name="tipo" class="block w-full rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132]">
                                <option value="">Todos (Altas y Bajas)</option>
                                <option value="ALTA">Solo Altas</option>
                                <option value="BAJA">Solo Bajas</option>
                            </select>
                        </div>

                        <button type="submit" class="bg-[#621132] hover:bg-[#4d0d27] text-white font-bold py-2 px-4 rounded shadow">
                            Descargar Excel
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
