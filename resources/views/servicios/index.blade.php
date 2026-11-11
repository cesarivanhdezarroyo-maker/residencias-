<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mis servicios registrados') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <p class="text-sm text-gray-600">
                Aquí aparecen los servicios tecnológicos que tienes activos: los que registraste tú y los que se aprobaron
                desde tus solicitudes. Registrar un servicio aquí <strong>no genera una solicitud</strong>.
                En los servicios aprobados puedes consultar el usuario y la contraseña que te asignó el área de Sistemas.
            </p>

            {{-- Servicios activos --}}
            <div class="bg-white shadow rounded-lg p-6 border-t-4 border-[#621132]">
                <h3 class="text-lg font-bold text-[#621132] mb-4">Servicios activos</h3>

                @if($activos->isEmpty())
                    <p class="text-sm text-gray-500">Todavía no tienes servicios registrados.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs text-gray-500 border-b">
                                    <th class="py-2 pr-3">Servicio</th>
                                    <th class="pr-3">Categoría</th>
                                    <th class="pr-3">Usuario o cuenta</th>
                                    <th class="pr-3">Contraseña</th>
                                    <th class="pr-3">Origen</th>
                                    <th class="pr-3">Fecha</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activos as $s)
                                    @php
                                        $usuarioMostrado = ($s->origen === 'solicitud' && $s->solicitud && $s->solicitud->credencial_usuario)
                                            ? $s->solicitud->credencial_usuario
                                            : $s->identificador;
                                        $conPassword = $s->origen === 'solicitud' && $s->solicitud && $s->solicitud->credencial_password;
                                    @endphp
                                    <tr class="border-b align-top">
                                        <td class="py-2 pr-3">{{ $s->servicio }}</td>
                                        <td class="pr-3">{{ $s->categoria }}</td>
                                        <td class="pr-3">
                                            {{ $usuarioMostrado ?? '—' }}
                                            @if($s->notas)
                                                <div class="text-xs text-gray-500 italic">{{ $s->notas }}</div>
                                            @endif
                                        </td>
                                        <td class="pr-3 whitespace-nowrap">
                                            @if($conPassword)
                                                <span id="pw-{{ $s->id }}" class="font-mono text-xs">••••••••</span>
                                                <button type="button" data-ver="{{ $s->id }}" class="text-xs text-[#621132] underline ml-1">Mostrar</button>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="pr-3">
                                            @if($s->origen === 'solicitud')
                                                <span class="text-xs font-bold bg-green-100 text-green-800 rounded-full px-2 py-0.5">Aprobado por solicitud</span>
                                            @else
                                                <span class="text-xs font-bold bg-gray-100 text-gray-700 rounded-full px-2 py-0.5">Registrado por ti</span>
                                            @endif
                                        </td>
                                        <td class="pr-3 whitespace-nowrap">{{ $s->created_at->format('d/m/Y') }}</td>
                                        <td class="text-right">
                                            @if($s->origen === 'declarado')
                                                <form action="{{ route('servicios.destroy', $s->id) }}" method="POST"
                                                      onsubmit="return confirm('¿Quitar este registro?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs text-red-600 underline">Quitar</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-gray-500 mt-3">No compartas tu contraseña. Se oculta sola a los 30 segundos de mostrarla.</p>
                @endif
            </div>

            {{-- Agregar un servicio que ya tengo --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-bold text-[#621132] mb-1">Agregar un servicio que ya tengo</h3>
                <p class="text-xs text-gray-500 mb-4">
                    Es solo un registro informativo. Escribe tu <strong>usuario, correo o nombre del equipo</strong>;
                    <strong>no escribas contraseñas</strong>. No sustituye a una solicitud.
                </p>

                <form action="{{ route('servicios.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf

                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Servicio</label>
                        <select id="servicio" name="servicio" required
                                class="w-full rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] text-sm">
                            <option value="">Selecciona un servicio</option>
                            @foreach($catalogo as $nombre => $categorias)
                                <option value="{{ $nombre }}" {{ old('servicio') === $nombre ? 'selected' : '' }}>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Categoría</label>
                        <select id="categoria" name="categoria" required
                                class="w-full rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] text-sm">
                            <option value="">Selecciona una categoría</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Usuario, correo o nombre del equipo</label>
                        <input type="text" name="identificador" value="{{ old('identificador') }}" maxlength="150"
                               class="w-full rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] text-sm">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Notas (opcional)</label>
                        <input type="text" name="notas" value="{{ old('notas') }}" maxlength="500"
                               class="w-full rounded border-gray-300 shadow-sm focus:border-[#621132] focus:ring-[#621132] text-sm">
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit" class="bg-[#621132] hover:bg-[#4d0d27] text-white font-bold py-2 px-6 rounded shadow text-sm">
                            Registrar servicio
                        </button>
                    </div>
                </form>
            </div>

            {{-- Dados de baja --}}
            @if($bajas->isNotEmpty())
                <div class="bg-white shadow rounded-lg p-6 border-t-4 border-gray-400">
                    <h3 class="text-lg font-bold text-gray-700 mb-3">Dados de baja</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        @foreach($bajas as $s)
                            <li>{{ $s->servicio }} — {{ $s->categoria }}
                                @if($s->identificador) ({{ $s->identificador }}) @endif
                                <span class="text-xs text-gray-400">· actualizado el {{ $s->updated_at->format('d/m/Y') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <script>
        // Las categorías dependen del servicio elegido
        const CATALOGO = @json($catalogo);
        const CATEGORIA_ANTERIOR = @json(old('categoria'));
        const selServicio = document.getElementById('servicio');
        const selCategoria = document.getElementById('categoria');

        function cargarCategorias() {
            const categorias = CATALOGO[selServicio.value] || [];
            selCategoria.innerHTML = '<option value="">Selecciona una categoría</option>';
            categorias.forEach(function (c) {
                const op = document.createElement('option');
                op.value = c;
                op.textContent = c;
                if (c === CATEGORIA_ANTERIOR) { op.selected = true; }
                selCategoria.appendChild(op);
            });
        }

        selServicio.addEventListener('change', cargarCategorias);
        cargarCategorias();

        // Mostrar la contraseña asignada: se pide al servidor solo al pulsar "Mostrar" y se oculta a los 30 segundos
        var URL_PLANTILLA = @json(route('servicios.credencial', ['id' => 0]));
        var metaToken = document.querySelector('meta[name="csrf-token"]');
        var campoToken = document.querySelector('input[name="_token"]');
        var TOKEN = metaToken ? metaToken.getAttribute('content') : (campoToken ? campoToken.value : '');

        document.querySelectorAll('[data-ver]').forEach(function (boton) {
            var id = boton.getAttribute('data-ver');
            var celda = document.getElementById('pw-' + id);
            var temporizador = null;

            function ocultar() {
                clearTimeout(temporizador);
                celda.textContent = '\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022';
                boton.textContent = 'Mostrar';
                boton.dataset.visible = '0';
            }

            boton.addEventListener('click', function () {
                if (boton.dataset.visible === '1') { ocultar(); return; }

                fetch(URL_PLANTILLA.replace('/0/', '/' + id + '/'), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.ok ? r.json() : Promise.reject(); })
                .then(function (d) {
                    celda.textContent = d.password ? d.password : '(sin contraseña)';
                    boton.textContent = 'Ocultar';
                    boton.dataset.visible = '1';
                    temporizador = setTimeout(ocultar, 30000);
                })
                .catch(function () { celda.textContent = 'No disponible'; });
            });
        });
    </script>
</x-app-layout>
