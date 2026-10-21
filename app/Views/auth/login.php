<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GSO Inventory Login</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, "Segoe UI", Arial, sans-serif;
            color: #111827;
            background: #f3f4f6;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background:
                radial-gradient(ellipse at 15% 10%, rgba(59, 130, 246, 0.09), transparent 35%),
                #f3f4f6;
        }
        .card {
            width: min(100%, 440px);
            padding: 40px;
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 14px;
            box-shadow: 0 18px 45px rgba(17, 24, 39, 0.09);
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 32px;
        }
        .brand img {
            width: 64px;
            height: 64px;
            padding: 5px;
            object-fit: contain;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #fff;
        }
        .brand-name {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }
        .brand-subtitle {
            margin: 5px 0 0;
            color: #6b7280;
            font-size: 0.78rem;
        }
        h1 {
            margin: 0;
            font-size: 1.55rem;
            letter-spacing: -0.03em;
        }
        .intro {
            margin: 8px 0 25px;
            color: #6b7280;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        .error {
            margin: 0 0 18px;
            padding: 11px 13px;
            border: 1px solid #fecaca;
            border-radius: 7px;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 0.85rem;
        }
        label {
            display: block;
            margin: 17px 0 7px;
            color: #374151;
            font-size: 0.82rem;
            font-weight: 600;
        }
        input, button {
            width: 100%;
            min-height: 44px;
            padding: 11px 13px;
            border-radius: 7px;
            font: inherit;
        }
        input {
            border: 1px solid #d1d5db;
            background: #fff;
            color: #111827;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        input::placeholder { color: #9ca3af; }
        input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        button {
            margin-top: 24px;
            border: 0;
            background: #2563eb;
            color: #fff;
            font-size: 0.92rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.15s, transform 0.15s;
        }
        button:hover { background: #1d4ed8; }
        button:active { transform: translateY(1px); }
        .card-footer {
            margin-top: 28px;
            padding-top: 17px;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 0.72rem;
            text-align: center;
        }
        @media (max-width: 480px) {
            body { padding: 16px; }
            .card { padding: 28px 23px; }
            .brand { margin-bottom: 27px; }
            .brand img { width: 56px; height: 56px; }
        }
    </style>
</head>
<body>
<div class="card">
    <div class="brand">
        <img src="images/logo1.png" alt="Provincial General Services Office logo">
        <div>
            <p class="brand-name">GSO Inventory</p>
            <p class="brand-subtitle">Provincial General Services Office</p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <p class="error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <h1>Welcome back</h1>
        <p class="intro">Sign in to continue to your property inventory dashboard.</p>

        <label for="username">Username</label>
        <input id="username" name="username" required
               value="<?= htmlspecialchars((string)($username ?? ''), ENT_QUOTES, 'UTF-8') ?>"
               autocomplete="username">

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required
               autocomplete="current-password">

        <button type="submit">Sign In</button>
    </form>

    <div class="card-footer">Secure access to the GSO property and inventory ledger</div>
</div>
</body>
</html>
