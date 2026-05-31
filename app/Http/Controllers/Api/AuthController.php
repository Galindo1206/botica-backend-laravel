<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\PasswordResetCode;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\EmailVerificationCode;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate(
            [
                'role_id' => 'nullable|exists:roles,id',
                'name' => 'required|string|max:120',
                'email' => 'required|email|max:150|unique:users,email',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/[A-Z]/',
                    'regex:/[0-9]/',
                ],
                'phone' => 'nullable|string|max:30',
            ],
            [
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.regex' => 'La contraseña debe contener al menos una mayúscula y un número.',
            ]
        );

        $user = User::create($data);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user' => $user->load('role'),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'La cuenta esta desactivada. Contacte al administrador.',
            ], 403);
        }

        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'Debe verificar su correo electrónico antes de iniciar sesión.'
            ], 403);
        }
        $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login correcto',
            'user' => $user->load('role'),
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json(
            $request->user()->load('role')
        );
    }

    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Todas las sesiones cerradas'
        ]);
    }
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        $code = rand(100000, 999999);

        PasswordResetCode::updateOrCreate(
            ['email' => $request->email],
            [
                'code' => $code,
                'expires_at' => Carbon::now()->addMinutes(15)
            ]
        );

        Mail::raw(
            "Tu código de recuperación es: {$code}\n\nVálido por 15 minutos.",
            function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Recuperación de contraseña');
            }
        );

        return response()->json([
            'message' => 'Código enviado al correo'
        ]);
    }
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required',
            'password' => 'required|min:6'
        ]);

        $resetCode = PasswordResetCode::where('email', $request->email)
            ->where('code', $request->code)
            ->first();

        if (!$resetCode) {
            return response()->json([
                'message' => 'Código inválido'
            ], 400);
        }

        if (Carbon::now()->gt($resetCode->expires_at)) {
            return response()->json([
                'message' => 'Código expirado'
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        $user->tokens()->delete();
        $resetCode->delete();


        return response()->json([
            'message' => 'Contraseña actualizada correctamente'
        ]);
    }
    public function sendVerificationCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'El correo ya está verificado'
            ], 400);
        }

        $code = rand(100000, 999999);

        EmailVerificationCode::updateOrCreate(
            ['email' => $request->email],
            [
                'code' => $code,
                'expires_at' => Carbon::now()->addMinutes(15)
            ]
        );

        Mail::raw(
            "Tu código de verificación es: {$code}\n\nVálido por 15 minutos.",
            function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Verificación de correo');
            }
        );

        return response()->json([
            'message' => 'Código de verificación enviado'
        ]);
    }
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required'
        ]);

        $verification = EmailVerificationCode::where(
            'email',
            $request->email
        )
            ->where(
                'code',
                $request->code
            )
            ->first();

        if (!$verification) {
            return response()->json([
                'message' => 'Código inválido'
            ], 400);
        }

        if (Carbon::now()->gt($verification->expires_at)) {
            return response()->json([
                'message' => 'Código expirado'
            ], 400);
        }

        $user = User::where(
            'email',
            $request->email
        )->first();

        $user->update([
            'email_verified_at' => now()
        ]);

        $verification->delete();

        return response()->json([
            'message' => 'Correo verificado correctamente'
        ]);
    }
}
