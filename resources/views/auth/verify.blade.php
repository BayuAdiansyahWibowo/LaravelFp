<!-- resources/views/auth/verify.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Email</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { box-sizing: border-box; }
        body {
            background: #f2f4f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 480px;
            margin: 100px auto;
            background: #fff;
            border-radius: 8px;
            padding: 32px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 {
            margin-bottom: 16px;
            font-size: 24px;
            color: #333;
        }
        p {
            font-size: 16px;
            color: #666;
        }
        .btn {
            margin-top: 24px;
            padding: 12px 20px;
            font-size: 16px;
            color: white;
            background: #007bff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: #0056b3;
        }
        .message {
            margin-top: 16px;
            color: green;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verifikasi Email Anda</h1>
        <p>Silakan klik link verifikasi yang telah kami kirim ke email Anda.</p>
        <p>Belum menerima email?</p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn">Kirim Ulang Link</button>
        </form>

        @if (session('message'))
            <div class="message">
                {{ session('message') }}
            </div>
        @endif
    </div>
</body>
</html>
