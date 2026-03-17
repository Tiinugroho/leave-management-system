<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Endpoint Login
     */
    public function login(Request $request)
    {
        // 1. Validasi input dengan pesan error yang spesifik per-field
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'password' => 'required|string',
            ],
            [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'password.required' => 'Password wajib diisi.',
                'password.string' => 'Format password tidak valid.',
            ],
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => 'Validasi gagal. Silakan periksa kembali inputan Anda.',
                    'errors' => $validator->errors(), // Menampilkan error per-field
                ],
                422,
            );
        }

        // 2. Cek apakah user dengan email tersebut ada
        $user = User::where('email', $request->email)->first();

        // 3. Verifikasi password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(
                [
                    'message' => 'Email atau password yang Anda masukkan salah.',
                    'errors' => [
                        'credentials' => ['Email atau password salah.'],
                    ],
                ],
                401,
            );
        }

        // 4. Buat Personal Access Token (PAT) menggunakan Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(
            [
                'message' => 'Login berhasil.',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ],
            200,
        );
    }

    /**
     * Endpoint Logout
     */
    public function logout(Request $request)
    {
        // Menghapus token yang sedang digunakan (Revoke Token)
        $request->user()->currentAccessToken()->delete();

        return response()->json(
            [
                'message' => 'Logout berhasil.',
            ],
            200,
        );
    }
}
