<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login Admin</title>
    <style>
        :root {
            --radius-sm: 6px;
            --radius-button: 9999px;
        }
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f8fafc;
            color: #111827;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-box {
            max-width: 360px;
            width: 100%;
            padding: 2rem;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: var(--radius-sm);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .login-box h1 {
            font-size: 1.75rem;
            margin-bottom: 1rem;
            text-align: center;
            color: var(--brand-primary);
        }
        label {
            display: block;
            margin-top: .75rem;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: .75rem 1rem;
            margin-top: .25rem;
            border: 1px solid #d1d5db;
            border-radius: var(--radius-sm);
        }
        input:focus {
            outline: 3px solid #2563eb;
        }
        button {
            width: 100%;
            margin-top: 1rem;
            padding: .75rem 1rem;
            background: var(--brand-primary);
            color: #fff;
            border: none;
            border-radius: var(--radius-button);
            font-weight: 700;
            cursor: pointer;
            transition: background .2s;
        }
        button:hover {
            background: var(--brand-primary-hover);
        }
        .error {
            color: #b91c1c;
            margin-top: .5rem;
        }
    </style>
</head>
<body>
<main class="login-box">
    <h1>Login Admin</h1>
    @if($errors->any())
        <div class="error" role="alert">{{ $errors->first() }}</div>
    @endif
    <form method="post" action="/admin/login">
        @csrf
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required autocomplete="current-password">
        <button type="submit">Masuk</button>
    </form>
</main>
</body>
</html>