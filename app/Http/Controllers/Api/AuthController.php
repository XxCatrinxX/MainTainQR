<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string']
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        if ($user->estado !== 'activo') {
            return response()->json([
                'message' => 'Usuario inactivo'
            ], 403);
        }

        // Borra tokens viejos (opcional)
        $user->tokens()->delete();

        $token = $user->createToken('android')->plainTextToken;

        return response()->json([
            'message' => 'Login OK',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre ?? null,
                'email' => $user->email,
                'rol' => $user->rol ?? null,
            ]
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logout OK']);
    }
}
// Este controlador maneja la autenticación de usuarios para la API. Permite a los usuarios iniciar sesión, obtener su información y cerrar sesión.
