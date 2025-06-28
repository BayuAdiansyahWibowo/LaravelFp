@extends('layouts.auth')

@section('content')
<style>
    body {
        background: #f4f7fe;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .login-container {
        display: flex;
        height: 100vh;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .login-card {
        display: flex;
        background: #fff;
        box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        border-radius: 20px;
        overflow: hidden;
        max-width: 900px;
        width: 100%;
        min-height: 480px;
    }

    .login-image {
        background: url('/images/loginfoto.svg') no-repeat center center;
        background-size: cover;
        width: 50%;
    }

    .login-form {
        padding: 40px 30px;
        background: #f9b233;
        width: 50%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .login-form h2 {
        margin-bottom: 20px;
        color: #0a1e51;
        font-size: 24px;
        font-weight: bold;
        text-align: center;
    }

    .login-form form {
        width: 100%;
    }

    .form-control {
    border-radius: 25px;
    padding: 10px 18px;
    font-size: 15px;
    margin-bottom: 16px;
    width: 100%;
    max-width: 95%;
    margin-left: auto;
    margin-right: auto;
}

    .btn-login {
    background: #0a1e51;
    color: white;
    border-radius: 25px;
    padding: 10px;
    font-size: 15px;
    font-weight: 600;
    border: none;
    width: 100%;
    max-width: 95%;
    margin-left: auto;
    margin-right: auto;
}


    .text-center {
        text-align: center;
        font-size: 13px;
        margin-top: 16px;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px 14px;
        border-radius: 5px;
        margin-bottom: 16px;
        text-align: center;
        font-size: 14px;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="login-container">
    <div class="login-card">
        <div class="login-image"></div>
        <div class="login-form">
            <h2>Login ke VEKTOR</h2>

            {{-- Error Message --}}
            @if ($errors->any())
                <div class="alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <input type="email" class="form-control" name="email" placeholder="Email" required autofocus>
                <input type="password" class="form-control" name="password" placeholder="Password" required>

                <div style="display:flex; justify-content: space-between; font-size:13px; margin-bottom: 16px;">
                    <label><input type="checkbox" name="remember"> Ingat saya</label>
                    <a href="{{ route('password.request') }}">Lupa password?</a>
                </div>

                <button type="submit" class="btn-login">Login</button>
            </form>

            <p class="text-center">Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a></p>
        </div>
    </div>
</div>

{{-- SweetAlert2 Success --}}
@if(session('status') || session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'success',
            title: 'Sukses!',
            text: "{{ session('status') ?? session('success') }}",
            timer: 4000,
            timerProgressBar: true,
            showConfirmButton: false,
            background: '#d4edda',
            color: '#155724'
        });
    });
</script>
@endif
@endsection
