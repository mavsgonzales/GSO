<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Users | GSO Inventory</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="sidebar.css">
    <style>
        * { box-sizing: border-box; }
        body {
            display: flex;
            min-height: 100vh;
            margin: 0;
            background: #fff;
            color: #111827;
            font-family: 'Inter', system-ui, sans-serif;
        }
        .app-content { display: flex; flex: 1; flex-direction: column; min-width: 0; }
        header {
            position: sticky;
            top: 0;
            z-index: 5;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 1rem;
            padding: 1rem 1.75rem;
            background: #fff;
            border-bottom: 1px solid #d1d5db;
        }
        header h1 { margin: 0; font-size: 1.15rem; }
        header > div { margin-left: auto; }
        header span { color: #4b5563; font-size: 0.82rem; }
        main { flex: 1; width: 100%; padding: 1.5rem 1.75rem; }
        .users-card {
            overflow: hidden;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
            margin-bottom: 1.5rem;
        }
        .users-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #d1d5db;
            background: #f9fafb;
        }
        .users-card-head h2 { margin: 0; font-size: 1rem; }
        .users-count { color: #4b5563; font-size: 0.82rem; }
        .account-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            align-items: end;
            gap: 1rem;
            padding: 1.25rem;
        }
        .account-form h2 { grid-column: 1 / -1; margin: 0; font-size: 1rem; }
        .form-field { display: grid; gap: 0.4rem; }
        .form-field label { color: #374151; font-size: 0.8rem; font-weight: 600; }
        .form-field input, .form-field select {
            width: 100%;
            min-height: 42px;
            padding: 0.6rem 0.7rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fff;
            color: #111827;
            font: inherit;
        }
        .form-field input:focus, .form-field select:focus {
            border-color: #3b82f6;
            outline: 3px solid rgba(59, 130, 246, 0.15);
        }
        .button {
            min-height: 42px;
            padding: 0.65rem 1rem;
            border: 0;
            border-radius: 6px;
            background: #2563eb;
            color: #fff;
            font: inherit;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
        }
        .button:hover { background: #1d4ed8; }
        .notice { margin: 1rem 1.25rem 0; padding: 0.75rem 1rem; border-radius: 6px; font-size: 0.85rem; }
        .notice-error { border: 1px solid #fecaca; background: #fef2f2; color: #b91c1c; }
        .notice-success { border: 1px solid #a7f3d0; background: #ecfdf5; color: #047857; }
        .table-scroll { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 0.8rem 1.25rem; border-bottom: 1px solid #e5e7eb; }
        th {
            background: #f3f4f6;
            color: #111827;
            font-size: 0.72rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        td { color: #1f2937; font-size: 0.88rem; }
        tbody tr:last-child td { border-bottom: 0; }
        .empty-state { padding: 2rem; color: #4b5563; text-align: center; }
        @media (max-width: 760px) {
            body { flex-direction: column; }
            header { flex-wrap: wrap; padding: 1rem; }
            main { padding: 1rem; }
        }
    </style>
</head>
<body>
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>
    <div class="app-content">
        <header>
            <h1>Users</h1>
            <div>
                <span>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </header>
        <main>
            <section class="users-card" aria-labelledby="create-user-heading">
                <?php if ($error !== ''): ?>
                    <p class="notice notice-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <?php if ($success !== ''): ?>
                    <p class="notice notice-success" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <form class="account-form" method="POST" action="users.php">
                    <h2 id="create-user-heading">Add Department User</h2>
                    <div class="form-field">
                        <label for="new-username">Username</label>
                        <input id="new-username" name="username" required minlength="3" maxlength="50"
                               pattern="[A-Za-z0-9_]{3,50}" autocomplete="off"
                               value="<?= htmlspecialchars($usernameInput, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-field">
                        <label for="new-department">Department</label>
                        <select id="new-department" name="department" required>
                            <option value="">Select department</option>
                            <?php foreach ($departmentChoices as $dept): ?>
                                <option value="<?= htmlspecialchars($dept, ENT_QUOTES, 'UTF-8') ?>"
                                    <?= $departmentInput === $dept ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="new-password">Temporary password</label>
                        <input id="new-password" type="password" name="password" required minlength="8"
                               autocomplete="new-password">
                    </div>
                    <button class="button" type="submit">Create User</button>
                </form>
            </section>
            <section class="users-card" aria-labelledby="users-heading">
                <div class="users-card-head">
                    <h2 id="users-heading">Registered Accounts</h2>
                    <span class="users-count"><?= count($users) ?> users</span>
                </div>
                <?php if ($users): ?>
                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th scope="col">Username</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Department</th>
                                    <th scope="col">Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($u['role'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($u['department'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($u['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="empty-state">No user accounts found.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
