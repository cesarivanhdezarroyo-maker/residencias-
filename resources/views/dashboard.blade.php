<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Trámites y Servicios - ISSSTE C.H. Xalapa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-[#621132]">
                <div class="p-8 text-gray-900">

                    <div class="mb-6 border-b pb-4">
                        <h3 class="text-2xl font-bold text-[#621132]">
                            {{ isset($solicitud) ? 'Corregir Solicitud Rechazada' : 'Nueva Solicitud de Servicio TI' }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            @if(isset($solicitud))
                                El servicio, puesto y tipo de movimiento no se pueden cambiar aquí -- solo corrige la evidencia y/o la justificación, y vuelve a enviarla para revisión.
                            @else
                                Llena los siguientes datos para generar tu formato oficial. Al finalizar, el sistema descargará automáticamente tu solicitud en formato Excel.
                            @endif
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-6 bg-red-50 border-l-4 border-red-600 p-4">
                            <div class="flex">
                                <div class="ml-3">
                                    <p class="text-sm text-red-700 font-bold">Por favor, revisa los siguientes detalles:</p>
                                    <ul class="mt-1 list-disc list-inside text-sm text-red-600">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <p class="mt-2 text-xs text-red-500 italic">
                                        Nota: por seguridad del navegador, el archivo de evidencia no se puede recordar automáticamente -- solo ese campo hay que volver a seleccionarlo.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- MUY IMPORTANTE: enctype="multipart/form-data" permite enviar archivos -->
                    <form id="formulario-solicitud" method="POST"
                          action="{{ isset($solicitud) ? route('solicitudes.update', $solicitud->id) : route('solicitudes.store') }}"
                          enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @if(isset($solicitud))
                            @method('PUT')
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            @if(isset($solicitud))
                                <!-- ===== MODO CORREGIR: los 4 campos se muestran fijos, no editables ===== -->
                                <div>
                                    <x-input-label :value="__('Tipo de Movimiento')" class="font-bold text-gray-700" />
                                    <p class="mt-1 block w-full rounded-md bg-gray-100 border border-gray-300 px-3 py-2 text-sm text-gray-700">{{ $solicitud->tipo_solicitud }}</p>
                                </div>
                                <div>
                                    <x-input-label :value="__('Puesto Oficial Actual')" class="font-bold text-gray-700" />
                                    <p class="mt-1 block w-full rounded-md bg-gray-100 border border-gray-300 px-3 py-2 text-sm text-gray-700">{{ $solicitud->usuario->puesto->nombre_puesto ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <x-input-label :value="__('Servicio Solicitado')" class="font-bold text-gray-700" />
                                    <p class="mt-1 block w-full rounded-md bg-gray-100 border border-gray-300 px-3 py-2 text-sm text-gray-700">{{ $solicitud->servicio->nombre_servicio ?? '' }}</p>
                                </div>
                                <div>
                                    <x-input-label :value="__('Categoría del Servicio')" class="font-bold text-gray-700" />
                                    <p class="mt-1 block w-full rounded-md bg-gray-100 border border-gray-300 px-3 py-2 text-sm text-gray-700">{{ $solicitud->categoria->nombre_categoria ?? '' }}</p>
                                </div>
                            @else
                                <!-- ===== MODO NUEVA SOLICITUD: los 4 campos son editables, igual que siempre ===== -->
                                <div>
                                    <x-input-label for="tipo_solicitud" :value="__('Tipo de Movimiento')" class="font-bold text-gray-700" />
                                    <select id="tipo_solicitud" name="tipo_solicitud" class="mt-1 block w-full border-gray-300 focus:border-[#621132] focus:ring-[#621132] rounded-md shadow-sm" required>
                                        <option value="" disabled {{ old('tipo_solicitud') ? '' : 'selected' }}>Seleccione una opción...</option>
                                        <option value="ALTA" {{ old('tipo_solicitud') == 'ALTA' ? 'selected' : '' }}>ALTA</option>
                                        <option value="BAJA" {{ old('tipo_solicitud') == 'BAJA' ? 'selected' : '' }}>BAJA</option>
                                    </select>
                                </div>

                                <div>
                                    <x-input-label for="puesto_id" :value="__('Puesto Oficial Actual')" class="font-bold text-gray-700" />
                                    <select id="puesto_id" name="puesto_id" class="mt-1 block w-full border-gray-300 focus:border-[#621132] focus:ring-[#621132] rounded-md shadow-sm" required>
                                        <option value="" disabled {{ old('puesto_id') ? '' : 'selected' }}>Seleccione su puesto oficial del catálogo...</option>
                                        @foreach($puestos as $puesto)
                                            <option value="{{ $puesto->id }}" {{ old('puesto_id') == $puesto->id ? 'selected' : '' }}>{{ $puesto->nombre_puesto }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <x-input-label for="servicio" :value="__('Servicio Solicitado')" class="font-bold text-gray-700" />
                                    <select id="servicio" name="servicio" class="mt-1 block w-full border-gray-300 focus:border-[#621132] focus:ring-[#621132] rounded-md shadow-sm" required onchange="actualizarCategorias()">
                                        <option value="" disabled {{ old('servicio') ? '' : 'selected' }}>Seleccione el servicio...</option>
                                        @foreach($serviciosCatalogo as $clave => $info)
                                            <option value="{{ $clave }}" {{ old('servicio') == $clave ? 'selected' : '' }}>{{ $info['nombre'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <x-input-label for="categoria" :value="__('Categoría del Servicio')" class="font-bold text-gray-700" />
                                    <select id="categoria" name="categoria" class="mt-1 block w-full border-gray-300 focus:border-[#621132] focus:ring-[#621132] rounded-md shadow-sm" required>
                                        <option value="" disabled selected>Primero seleccione un servicio...</option>
                                    </select>
                                </div>
                            @endif
                        </div>

                        <!-- 5. Evidencia (Imagen o PDF) -->
                        <div class="pt-2 border-t border-gray-200">
                            <x-input-label for="imagen_evidencia" :value="__('Subir Captura/Imagen de Evidencia')" class="font-bold text-gray-700" />
                            <input type="file" id="imagen_evidencia" name="imagen_evidencia" accept="image/png, image/jpeg, image/jpg, application/pdf" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[#621132] file:text-white hover:file:bg-[#4d0d27] cursor-pointer" required>
                            <p class="mt-1 text-xs text-gray-500">Formatos permitidos: JPG, JPEG, PNG o PDF (una sola página). Tamaño máximo: 2MB.</p>
                        </div>

                        <!-- 6. Justificación -->
                        <div class="pt-2">
                            <x-input-label for="justificacion" :value="__('Justificación de la Solicitud')" class="font-bold text-gray-700" />
                            <textarea id="justificacion" name="justificacion" rows="4" class="mt-1 block w-full border-gray-300 focus:border-[#621132] focus:ring-[#621132] rounded-md shadow-sm" required placeholder="Explique brevemente el motivo por el cual solicita este trámite...">{{ old('justificacion', $solicitud->justificacion ?? '') }}</textarea>
                        </div>

                        <!-- Botón de Envío -->
                        <div class="flex items-center justify-end mt-6 pt-6 border-t border-gray-200">
                            <button type="submit" id="boton-generar" class="inline-flex items-center px-6 py-3 bg-[#621132] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#4d0d27] focus:outline-none transition ease-in-out duration-150">
                                <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                {{ isset($solicitud) ? __('Reenviar Solicitud Corregida') : __('Generar Solicitud en Excel') }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    @unless(isset($solicitud))
        <!-- SCRIPT DE FILTRADO DINÁMICO Y CARGA (solo aplica en modo Nueva Solicitud) -->
        <script>
            const catalogo = @json($serviciosCatalogo);

            function actualizarCategorias(categoriaPreseleccionada) {
                const servicioSelect = document.getElementById('servicio');
                const categoriaSelect = document.getElementById('categoria');
                const servicioElegido = servicioSelect.value;

                categoriaSelect.innerHTML = '<option value="" disabled selected>Seleccione la categoría...</option>';

                if (servicioElegido && catalogo[servicioElegido]) {
                    const categorias = catalogo[servicioElegido].categorias;
                    for (const [clave, nombre] of Object.entries(categorias)) {
                        const opt = document.createElement('option');
                        opt.value = clave;
                        opt.textContent = nombre;
                        categoriaSelect.appendChild(opt);
                    }
                    if (categoriaPreseleccionada) {
                        categoriaSelect.value = categoriaPreseleccionada;
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                const servicioPrevio = @json(old('servicio'));
                const categoriaPrevia = @json(old('categoria'));
                if (servicioPrevio) {
                    actualizarCategorias(categoriaPrevia);
                }
            });
        </script>
    @endunless

    <script>
        document.getElementById('formulario-solicitud').addEventListener('submit', function() {
            let boton = document.getElementById('boton-generar');
            boton.disabled = true;
            boton.classList.remove('bg-[#621132]', 'hover:bg-[#4d0d27]');
            boton.classList.add('bg-gray-500', 'cursor-not-allowed');
            boton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Procesando...
            `;
        });
    </script>
</x-app-layout>
