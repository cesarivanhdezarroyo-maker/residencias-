<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Personal - Panel TI') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                    <p class="font-bold">¡Éxito!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-[#621132]">
                <div class="p-6 text-gray-900">
                    <h3 class="text-2xl font-bold text-[#621132] mb-6">Cuentas Registradas en la Plataforma</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                <tr>
                                    <th class="py-3 px-4 text-left">Matrícula</th>
                                    <th class="py-3 px-4 text-left">Nombre del Personal</th>
                                    <th class="py-3 px-4 text-left">Correo Electrónico</th>
                                    <th class="py-3 px-4 text-center">Trámites Solicitados</th>
                                    <th class="py-3 px-4 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 text-sm font-light">
                                @foreach ($usuarios as $usuario)
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="py-3 px-4 text-left font-bold text-gray-800">{{ $usuario->numero_empleado }}</td>
                                        <td class="py-3 px-4 text-left">{{ $usuario->name }}</td>
                                        <td class="py-3 px-4 text-left">
                                            <div>{{ $usuario->email }}</div>
                                            @if($usuario->correo_institucional)
                                                <div class="text-xs text-[#621132] font-semibold mt-0.5">
                                                    {{ $usuario->correo_institucional }} <span class="text-gray-400 font-normal">(institucional)</span>
                                                </div>
                                            @endif
                                        </td>
                                        
                                        <!-- Cuenta los archivos reales -->
                                        <td class="py-3 px-4 text-center font-bold text-[#621132]">
                                            {{ $usuario->solicitudes->count() }}
                                        </td>
                                        
                                        <td class="py-3 px-4 text-center flex justify-center gap-2">
                                            <a href="{{ route('admin.solicitudes.usuario', $usuario->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded shadow text-xs">
                                                Ver Solicitudes
                                            </a>
                                            
                                            <form action="{{ route('admin.usuarios.eliminar', $usuario->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta cuenta y TODOS sus trámites?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded shadow text-xs">
                                                    Borrar Cuenta
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($usuarios->isEmpty())
                        <div class="text-center py-8 text-gray-500">
                            No hay personal registrado en la plataforma en este momento.
                        </div>
                    @endif
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>