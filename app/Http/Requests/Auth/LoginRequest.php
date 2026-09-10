<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numero_empleado' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Regla real: la matrícula (numero_empleado) sirve para entrar SOLO
        // mientras Sistemas no haya asignado credenciales definitivas. En cuanto
        // usuario_sistema queda asignado, esas nuevas credenciales SUSTITUYEN a
        // las de autorregistro — la matrícula deja de funcionar para entrar
        // (aunque como dato del empleado nunca se borra ni se edita).
        $input = $this->input('numero_empleado');

        $usuario = Usuario::where('usuario_sistema', $input)
                        ->orWhere(function ($query) use ($input) {
                            $query->whereNull('usuario_sistema')
                                  ->where('numero_empleado', $input);
                        })
                        ->first();

        if (! $usuario || ! Auth::attempt([
                'numero_empleado' => $usuario->numero_empleado,
                'password' => $this->input('password'),
            ], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'numero_empleado' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'numero_empleado' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('numero_empleado')).'|'.$this->ip());
    }
}