@extends('layouts.auth')

@section('content')
<style>
    body {
        background: #f4f7fe;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .reset-container {
        display: flex;
        height: 100vh;
        align-items: center;
        justify-content: center;
    }
    .reset-card {
        background: #fff;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        border-radius: 16px;
        padding: 50px 40px;
        max-width: 400px;
        width: 100%;
        text-align: center;
    }
    .reset-card h2 {
        margin-bottom: 30px;
        color: #0a1e51;
    }
    .form-control {
        width: 100%;
        padding: 12px 16px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 15px;
        box-sizing: border-box;
    }
    .btn-reset {
        background: #f9b233;
        color: #0a1e51;
        border-radius: 8px;
        padding: 12px;
        border: none;
        font-weight: bold;
        font-size: 16px;
        width: 100%;
        transition: background-color 0.3s;
    }
    .btn-reset:hover {
        background-color: #e0a323;
    }
    .login-link {
        display: block;
        margin-top: 20px;
        color: #0a1e51;
        text-decoration: none;
        font-weight: 600;
    }
    .login-link:hover {
        text-decoration: underline;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="reset-container">
    <div class="reset-card">
        <h2>Reset Password</h2>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email', $email ?? '') }}" required autofocus>
            <input type="password" name="password" class="form-control" placeholder="Password Baru" required>
            <input type="password" name="password_confirmation" class="form-control" placeholder="Konfirmasi Password" required>

            <button type="submit" class="btn-reset">Reset Password</button>
        </form>

        <a href="{{ route('login') }}" class="login-link">Kembali ke Login</a>
    </div>
</div>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'success',
            title: 'Sukses!',
            text: "{{ session('success') }}",
            timer: 4000,
            timerProgressBar: true,
            showConfirmButton: false,
            background: '#d4edda',
            color: '#155724'
        });
    });
</script>
@endif

@if ($errors->any())
<div class="alert alert-danger mx-auto mt-3" style="max-width: 400px;">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
@endsection
