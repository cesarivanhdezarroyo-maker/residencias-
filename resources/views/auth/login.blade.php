<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Encabezado guinda del ISSSTE, con el logo dentro -->
    <div class="-mx-8 -mt-6 mb-6 bg-[#621132] py-8 px-8 flex flex-col items-center rounded-t-lg">
        <img src="{{ asset('images/logo-issste.png') }}" alt="ISSSTE" class="h-24 mb-3">
        <h2 class="text-lg font-bold text-white text-center">Cuentas TI - ISSSTE C.H. Xalapa</h2>
    </div>

    <p class="text-sm text-gray-500 text-center mb-4">Ingresa tus credenciales para continuar</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Número de Empleado (Matrícula) -->
        <div>
            <x-input-label for="numero_empleado" :value="__('Número de Empleado (Matrícula)')" />
            <x-text-input id="numero_empleado" class="block mt-1 w-full" type="text" name="numero_empleado" :value="old('numero_empleado')" required autofocus autocomplete="username" maxlength="6" />
            <x-input-error :messages="$errors->get('numero_empleado')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[#621132] shadow-sm focus:ring-[#621132]" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Recordarme') }}</span>
            </label>
        </div>

        <div class="flex flex-col items-center justify-end mt-6 space-y-4">
            <x-primary-button class="w-full justify-center bg-[#621132] hover:bg-[#4d0d27] py-3">
                {{ __('INICIAR SESIÓN') }}
            </x-primary-button>

            <p class="text-sm text-gray-600">
                ¿No tienes una cuenta? <a class="underline text-[#621132] hover:text-red-800 font-bold" href="{{ route('register') }}">Regístrate aquí</a>
            </p>
        </div>
    </form>
</x-guest-layout>
