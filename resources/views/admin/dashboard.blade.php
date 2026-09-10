<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Administración - Área de Sistemas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-[#621132]">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Módulo de Control</h3>
                    <p>Bienvenido Administrador. Aquí podrás ver la lista de usuarios, revisar sus talones de pago, generar los PDF de las cédulas y actualizar las credenciales del sistema.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>