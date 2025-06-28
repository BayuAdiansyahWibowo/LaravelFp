@extends('layouts.auth')

@section('content')
<style>
    body {
        background: #f4f7fe;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .register-container {
        display: flex;
        height: 100vh;
        align-items: center;
        justify-content: center;
    }
    .register-card {
        display: flex;
        background: #fff;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        border-radius: 20px;
        overflow: hidden;
        max-width: 900px;
        width: 100%;
    }
    .register-image {
        background: url('/images/loginfoto.svg') no-repeat center center;
        background-size: cover;
        width: 50%;
    }
    .register-form {
        padding: 60px 40px;
        background: #f9b233;
        width: 50%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .register-form h2 {
        margin-bottom: 30px;
        color: #333;
    }
    .form-control {
        border-radius: 30px;
        padding: 10px 20px;
        margin-bottom: 20px;
        width: 95%;
        margin-left: auto;
        margin-right: auto;
    }
    .btn-register {
        background: #0a1e51;
        color: white;
        border-radius: 30px;
        padding: 10px;
        border: none;
        width: 95%;
        margin-left: auto;
        margin-right: auto;
    }
    .text-center {
        text-align: center;
        font-size: 14px;
    }
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
</style>

{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="register-container">
    <div class="register-card">
        <div class="register-image"></div>
        <div class="register-form">
            <h2>Buat Akun Baru</h2>

            @if ($errors->any())
                <div class="alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <input type="text" class="form-control" name="name" placeholder="Nama Lengkap" required>
                <input type="email" class="form-control" name="email" placeholder="Email" required>
                <input type="password" class="form-control" name="password" placeholder="Password" required>
                <input type="password" class="form-control" name="password_confirmation" placeholder="Konfirmasi Password" required>

                <button type="submit" class="btn-register">Daftar</button>
            </form>

            <p class="text-center mt-4">Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a></p>
        </div>
    </div>
</div>
@endsection
