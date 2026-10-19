<nav x-data="{ open: false }" class="bg-[#621132] border-b border-[#4d0d27] shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-white" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @if(Auth::user()->rol !== 'admin')
                        <a href="{{ route('solicitudes.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('solicitudes.index') ? 'border-white text-white font-bold' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-200' }} text-sm leading-5 transition duration-150 ease-in-out">
                            {{ __('Mis Solicitudes') }}
                        </a>
                        @php
                            $avisosNuevos = \App\Models\Mensaje::where('usuario_id', Auth::id())
                                ->where('de_admin', true)
                                ->whereNull('leido_at')
                                ->count();
                        @endphp
                        <a href="{{ route('mensajes.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('mensajes.*') ? 'border-white text-white font-bold' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-200' }} text-sm leading-5 transition duration-150 ease-in-out">
                            {{ __('Avisos') }}
                            @if($avisosNuevos > 0)
                                <span class="ms-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold text-[#621132] bg-yellow-300 rounded-full">{{ $avisosNuevos }}</span>
                            @endif
                        </a>
                    @endif

                    @if(Auth::user()->rol === 'admin')
                        <a href="{{ route('admin.usuarios.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.usuarios.index') ? 'border-white text-white font-bold' : 'border-transparent text-yellow-400 hover:text-yellow-300 hover:border-yellow-300' }} text-sm leading-5 transition duration-150 ease-in-out">
                            {{ __('Panel TI') }}
                        </a>
                        <a href="{{ route('admin.reportes.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.reportes.*') ? 'border-white text-white font-bold' : 'border-transparent text-yellow-400 hover:text-yellow-300 hover:border-yellow-300' }} text-sm leading-5 transition duration-150 ease-in-out">
                            {{ __('Reportes') }}
                        </a>
                        <a href="{{ route('admin.limpieza.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.limpieza.*') ? 'border-white text-white font-bold' : 'border-transparent text-yellow-400 hover:text-yellow-300 hover:border-yellow-300' }} text-sm leading-5 transition duration-150 ease-in-out">
                            {{ __('Limpieza') }}
                        </a>
                        <a href="{{ route('admin.oficio.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.oficio.*') ? 'border-white text-white font-bold' : 'border-transparent text-yellow-400 hover:text-yellow-300 hover:border-yellow-300' }} text-sm leading-5 transition duration-150 ease-in-out">
                            {{ __('Datos del Oficio') }}
                        </a>
                        <a href="{{ route('admin.configuracion.excel') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.configuracion.excel*') ? 'border-white text-white font-bold' : 'border-transparent text-yellow-400 hover:text-yellow-300 hover:border-yellow-300' }} text-sm leading-5 transition duration-150 ease-in-out">
                            {{ __('Datos del Excel') }}
                        </a>
                        @php
                            $mensajesPorLeer = \App\Models\Mensaje::where('de_admin', false)
                                ->whereNull('leido_at')
                                ->count();
                        @endphp
                        <a href="{{ route('admin.mensajes.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.mensajes.*') ? 'border-white text-white font-bold' : 'border-transparent text-yellow-400 hover:text-yellow-300 hover:border-yellow-300' }} text-sm leading-5 transition duration-150 ease-in-out">
                            {{ __('Mensajes') }}
                            @if($mensajesPorLeer > 0)
                                <span class="ms-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold text-[#621132] bg-yellow-300 rounded-full">{{ $mensajesPorLeer }}</span>
                            @endif
                        </a>

                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-bold rounded-md text-white bg-[#4d0d27] hover:bg-[#38091c] focus:outline-none transition ease-in-out duration-150 shadow-sm">
                            <div>{{ Auth::user()->name ?? 'Usuario' }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="block px-4 py-2 text-xs text-gray-400 border-b">
                            Matrícula: {{ Auth::user()->numero_empleado }}
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-700 font-bold hover:bg-red-50">
                                {{ __('Cerrar Sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>
