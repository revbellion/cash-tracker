<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Cash Tracker</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --bg-body: #0f172a;
            --bg-card: #273548;
            --text-primary: #ffffff;
            --text-muted: #94a3b8;
            --theme-primary: #3b82f6;
            --theme-primary-hover: #2563eb;
            --theme-primary-rgb: 59, 130, 246;
            --border-subtle: rgba(255,255,255,0.08);
        }
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        body {
            background: var(--bg-body);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-card {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .login-logo img { width: 72px; height: 72px; object-fit: contain; }
        .login-logo h1 { font-size: 1.25rem; font-weight: 800; color: #fff; margin-top: 0.75rem; letter-spacing: -0.5px; }
        .login-logo p { font-size: 0.8rem; color: var(--text-muted); margin: 0.25rem 0 0; }
        .form-label { color: var(--text-muted); font-size: 0.8rem; font-weight: 600; margin-bottom: 0.3rem; }
        .form-control {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            color: #fff;
            font-size: 0.9rem;
            padding: 0.6rem 0.85rem;
        }
        .form-control:focus {
            border-color: var(--theme-primary);
            box-shadow: 0 0 0 3px rgba(var(--theme-primary-rgb), 0.15);
            background: #0f172a;
            color: #fff;
        }
        .form-control::placeholder { color: #475569; }
        .btn-login {
            background: var(--theme-primary);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.65rem;
            width: 100%;
            transition: all 0.15s;
        }
        .btn-login:hover { background: var(--theme-primary-hover); transform: translateY(-1px); }
        .alert { border: none; border-radius: 8px; font-size: 0.8rem; }
        .remember-label { font-size: 0.8rem; color: var(--text-muted); cursor: pointer; }
        .remember-label input { margin-right: 0.4rem; }
        .footer-text { text-align: center; margin-top: 1.5rem; font-size: 0.7rem; color: var(--text-muted); }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <img src="{{ asset('logo.png') }}" alt="Logo">
            <h1>ADI CELL</h1>
            <p>Cash Tracker</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2 px-3 mb-3">
                <i class="fas fa-exclamation-circle me-1"></i> {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" autocomplete="off">
            @csrf
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username" value="{{ old('username') }}" autocomplete="off" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan password" autocomplete="off" required>
            </div>
            <div class="mb-3 d-flex align-items-center justify-content-between">
                <label class="remember-label">
                    <input type="checkbox" name="remember"> Ingat saya
                </label>
            </div>
            <button type="submit" class="btn-login">Masuk</button>
        </form>
        <div class="footer-text">ADI CELL &copy; {{ date('Y') }}</div>
    </div>
</body>
</html>
