<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Registro de Personal</h2>
        <p class="text-gray-500">ISSSTE - C.H. Xalapa</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Nombres -->
        <div>
            <x-input-label for="nombres" :value="__('Nombre(s)')" />
            <x-text-input id="nombres" class="block mt-1 w-full" type="text" name="nombres" :value="old('nombres')" required autofocus autocomplete="given-name" />
            <x-input-error :messages="$errors->get('nombres')" class="mt-2" />
        </div>

        <!-- Apellidos -->
        <div class="mt-4">
            <x-input-label for="apellidos" :value="__('Apellidos')" />
            <x-text-input id="apellidos" class="block mt-1 w-full" type="text" name="apellidos" :value="old('apellidos')" required autocomplete="family-name" />
            <x-input-error :messages="$errors->get('apellidos')" class="mt-2" />
        </div>

        <!-- Correo Electrónico -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Correo Electrónico Personal')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Número de Empleado (Matrícula) -->
        <div class="mt-4">
            <x-input-label for="numero_empleado" :value="__('Número de Empleado (Matrícula)')" />
            <x-text-input id="numero_empleado" class="block mt-1 w-full" type="text" name="numero_empleado" :value="old('numero_empleado')" required maxlength="6" />
            <x-input-error :messages="$errors->get('numero_empleado')" class="mt-2" />
        </div>

        <!-- Contraseña -->
        <div class="mt-4 flex gap-4">
            <div class="w-1/2">
                <x-input-label for="password" :value="__('Contraseña')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div class="w-1/2">
                <x-input-label for="password_confirmation" :value="__('Confirmar')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="flex flex-col items-center justify-end mt-6 space-y-4">
            <x-primary-button class="w-full justify-center bg-[#621132] hover:bg-[#4d0d27] py-3">
                {{ __('CREAR CUENTA') }}
            </x-primary-button>
            
            <p class="text-sm text-gray-600">
                ¿Ya tienes una cuenta? <a class="underline text-[#621132] hover:text-red-800 font-bold" href="{{ route('login') }}">Inicia sesión aquí</a>
            </p>
        </div>
    </form>
</x-guest-layout>