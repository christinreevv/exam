<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'regex:/^[А-Яа-яЁё\s\-]+$/u'],
            'surname' => ['required', 'regex:/^[А-Яа-яЁё\s\-]+$/u'],
            'patronymic' => ['nullable', 'regex:/^[А-Яа-яЁё\s\-]+$/u'],
            'login' => ['required', 'regex:/^[a-zA-Z0-9\-]+$/', 'unique:users,login'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6', 'confirmed'],
            'rules' => ['accepted'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'patronymic' => $request->patronymic,
            'login' => $request->login,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 0, // по умолчанию клиент
        ]);

        return response()->json([
            'message' => 'Регистрация прошла успешно!',
            'user' => $user
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('login', $request->login)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Неверный логин или пароль'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Вход выполнен успешно!',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user()->only(['id', 'name', 'surname', 'patronymic', 'email', 'role']);

        return response()->json([
            'user' => $user
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Вы успешно вышли из системы']);
    }
}
