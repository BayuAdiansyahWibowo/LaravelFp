<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Driver;
use App\Helpers\JwtHelper;
use Firebase\JWT\ExpiredException;

class AuthController extends Controller
{
    // -------------------------
    // ========== WEB ==========
    // -------------------------

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->withInput();
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'admin',
        ]);

        Auth::login($user);

        return redirect()->route('login')->with('success', 'Registrasi berhasil, silakan login!');
    }

    // -------------------------
    // ========== API ==========
    // -------------------------

    public function apiLogin(Request $request)
{
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required|string',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Email atau password salah.'], 401);
    }

    if ($user->role !== 'driver') {
        return response()->json(['message' => 'Akun ini bukan driver.'], 403);
    }

    $driver = $user->driver; // ambil driver lewat relasi

    if (!$driver) {
        return response()->json(['message' => 'Data driver tidak ditemukan.'], 404);
    }

    $payload = [
        'iss' => "tracking-app",
        'sub' => $user->id,
        'iat' => time(),
        'exp' => time() + (60 * 60 * 24), // 1 hari
        'data' => [
            'id'    => $driver->id,
            'email' => $user->email,
            'name'  => $user->name,
        ],
    ];

    $token = JwtHelper::encode($payload);

    return response()->json([
        'message' => 'Login berhasil',
        'token'   => $token,
        'driver'  => $driver,
    ]);
}

    public function apiRegister(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validasi gagal',
            'errors' => $validator->errors()->all()
        ], 422);
    }

    // Buat user baru
    $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => Hash::make($request->password),
        'role'     => 'driver'
    ]);

    // Buat driver terkait user_id
    $driver = Driver::create([
        'user_id'        => $user->id,
        'nama'           => $user->name,
        'email'          => $user->email,
        'nomor_telepon'  => $request->nomor_telepon ?? '-',
        'alamat'         => $request->alamat ?? '-',
        'status'         => 'aktif',
    ]);

    // Encode token berdasarkan user_id (bukan lagi id driver)
    $payload = [
        'iss' => "tracking-app",
        'sub' => $user->id,
        'iat' => time(),
        'exp' => time() + (60 * 60 * 24), // 1 hari
    ];

    $token = JwtHelper::encode($payload);

    return response()->json([
        'message' => 'Registrasi driver berhasil.',
        'token' => $token,
        'driver' => $driver,
    ], 201);
}

    public function forgot(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Link reset telah dikirim ke email.'])
            : response()->json(['message' => 'Gagal mengirim link reset.'], 500);
    }

    //reset password akun driver ionic
    public function resetPasswordFromApp(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'token' => 'required|string',
        'password' => 'required|min:6|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->save();
        }
    );

    if ($status === Password::PASSWORD_RESET) {
        return response()->json(['message' => 'Password berhasil direset.']);
    }

    return response()->json(['message' => 'Reset password gagal.', 'status' => $status], 400);
}



    public function logout(Request $request)
    {
        return response()->json(['message' => 'Logout berhasil, hapus token dari sisi client']);
    }

    public function driverProfile(Request $request)
    {
        $payload = $request->get('jwt_payload');
        $driver = $request->get('driver');

        if (!$driver) {
            return response()->json(['message' => 'Driver tidak ditemukan'], 404);
        }

        $user = $driver->user; // Mengambil user berdasarkan email

        return response()->json([
            'message' => 'Berhasil ambil profil',
            'data' => [
                'name' => $user?->name ?? $driver->nama,
                'email' => $user?->email ?? $driver->email,
                'alamat' => $driver->alamat,
                'latitude' => $driver->latitude,
                'longitude' => $driver->longitude,
                'nomor_telepon' => $driver->nomor_telepon,
                'status' => $driver->status
            ]
        ]);
    }



    // 🔥 Tambahan agar frontend bisa ambil nama & email driver
    public function apiUser(Request $request)
    {
        $driver = $request->get('driver');

        if (!$driver) {
            return response()->json(['message' => 'Data driver tidak ditemukan'], 404);
        }

        return response()->json([
            'name' => $driver->nama,
            'email' => $driver->email,
        ]);
    }
}
