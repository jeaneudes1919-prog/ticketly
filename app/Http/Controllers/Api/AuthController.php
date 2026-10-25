<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiTokenRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function token(ApiTokenRequest $request)
    {
        $d = $request->validated();
        $user = User::where('email', $d['email'])->first();
        if (! $user || ! $user->is_active || ! Hash::check($d['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['Identifiants incorrects.']]);
        }

        return response()->json(['token' => $user->createToken($d['device_name'])->plainTextToken, 'user' => $this->user($user)]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté.']);
    }

    public function me(Request $request)
    {
        return response()->json($this->user($request->user()));
    }

    private function user(User $u): array
    {
        return ['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'role' => $u->role->value];
    }
}
