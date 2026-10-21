<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Change Password | GSO Inventory</title>
    <link rel="stylesheet" href="sidebar.css">
    <style>
        * { box-sizing: border-box; }
        body {
            display: flex;
            min-height: 100vh;
            margin: 0;
            background: #f3f4f6;
            color: #111827;
            font-family: Inter, "Segoe UI", Arial, sans-serif;
        }
        .app-content { display: flex; flex: 1; flex-direction: column; min-width: 0; }
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.75rem;
            border-bottom: 1px solid #d1d5db;
            background: #fff;
        }
        header h1 { margin: 0; font-size: 1.15rem; }
        header span { color: #4b5563; font-size: 0.82rem; }
        main { width: min(100%, 720px); padding: 1.5rem 1.75rem; }
        .password-card {
            padding: 1.5rem;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        }
        .password-card h2 { margin: 0; font-size: 1rem; }
        .intro { margin: 0.5rem 0 1.25rem; color: #6b7280; font-size: 0.86rem; line-height: 1.5; }
        .form-field { display: grid; gap: 0.4rem; margin-top: 1rem; }
        label { color: #374151; font-size: 0.82rem; font-weight: 600; }
        input {
            width: 100%;
            min-height: 42px;
            padding: 0.6rem 0.7rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fff;
            color: #111827;
            font: inherit;
        }
        input:focus { border-color: #3b82f6; outline: 3px solid rgba(59, 130, 246, 0.15); }
        .actions { display: flex; flex-wrap: wrap; gap: 0.65rem; margin-top: 1.4rem; }
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0.6rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fff;
            color: #374151;
            font: inherit;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }
        .button-primary { border-color: #2563eb; background: #2563eb; color: #fff; }
        .button-primary:hover { border-color: #1d4ed8; background: #1d4ed8; }
        .button:not(.button-primary):hover { background: #f3f4f6; }
        .notice { margin: 0 0 1rem; padding: 0.75rem 1rem; border-radius: 6px; font-size: 0.85rem; }
        .notice-error { border: 1px solid #fecaca; background: #fef2f2; color: #b91c1c; }
        .notice-success { border: 1px solid #a7f3d0; background: #ecfdf5; color: #047857; }
        @media (max-width: 760px) {
            body { flex-direction: column; }
            header { flex-wrap: wrap; padding: 1rem; }
            main { padding: 1rem; }
        }
    </style>
</head>
<body>
    <?php if ($isAdmin) { require __DIR__ . '/../partials/sidebar.php'; } ?>
    <div class="app-content">
        <header>
            <h1>Change Password</h1>
            <span>Welcome, <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></span>
        </header>
        <main>
            <section class="password-card" aria-labelledby="password-heading">
                <h2 id="password-heading">Update your password</h2>
                <p class="intro">Enter your current password, then choose a new password with at least 8 characters.</p>
                <?php if ($error !== ''): ?>
                    <p class="notice notice-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <?php if ($success !== ''): ?>
                    <p class="notice notice-success" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <form method="POST" action="change_password.php">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="form-field">
                        <label for="current-password">Current password</label>
                        <input id="current-password" type="password" name="current_password" required
                               autocomplete="current-password">
                    </div>
                    <div class="form-field">
                        <label for="new-password">New password</label>
                        <input id="new-password" type="password" name="new_password" required minlength="8"
                               autocomplete="new-password">
                    </div>
                    <div class="form-field">
                        <label for="confirm-password">Confirm new password</label>
                        <input id="confirm-password" type="password" name="confirm_password" required minlength="8"
                               autocomplete="new-password">
                    </div>
                    <div class="actions">
                        <button class="button button-primary" type="submit">Save New Password</button>
                        <a class="button" href="index.php">Back to Dashboard</a>
                    </div>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
