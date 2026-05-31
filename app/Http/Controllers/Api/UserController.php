<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(
            User::with('role')
                ->orderBy('name')
                ->get()
        );
    }

    public function roles()
    {
        return response()->json(
            Role::where('is_active', true)
                ->orderBy('name')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
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
            'is_active' => 'boolean',
            'email_verified' => 'boolean',
        ], [
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'password.regex' => 'La contrasena debe contener al menos una mayuscula y un numero.',
        ]);

        $data['email_verified_at'] = $request->boolean('email_verified') ? now() : null;
        unset($data['email_verified']);

        $user = User::create($data);

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'data' => $user->load('role'),
        ], 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load('role'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role_id' => 'nullable|exists:roles,id',
            'name' => 'required|string|max:120',
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
            'phone' => 'nullable|string|max:30',
            'is_active' => 'boolean',
            'email_verified' => 'boolean',
        ], [
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'password.regex' => 'La contrasena debe contener al menos una mayuscula y un numero.',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        if ($request->has('email_verified')) {
            $data['email_verified_at'] = $request->boolean('email_verified') ? now() : null;
        }

        unset($data['email_verified']);

        $user->update($data);

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'data' => $user->load('role'),
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => 'No puedes desactivar tu propia cuenta desde esta sesion.',
            ], 422);
        }

        $user->update(['is_active' => false]);
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Usuario desactivado correctamente',
        ]);
    }

    public function recover(User $user)
    {
        $user->update(['is_active' => true]);

        return response()->json([
            'message' => 'Usuario recuperado correctamente',
            'data' => $user->load('role'),
        ]);
    }

    public function forceDestroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => 'No puedes borrar definitivamente tu propia cuenta desde esta sesion.',
            ], 422);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'Usuario borrado definitivamente',
        ]);
    }
}
