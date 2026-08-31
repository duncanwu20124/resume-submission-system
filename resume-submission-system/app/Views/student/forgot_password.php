<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>忘記密碼 | 學生甄選與志願媒合系統</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --surface: #ffffff;
            --background: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #cbd5e1;
            --danger: #ef4444;
            --success: #10b981;
            --radius-lg: 0.75rem;
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: var(--text-main);
        }

        .auth-card {
            background: var(--surface);
            max-width: 440px;
            width: 100%;
            border-radius: 1.25rem;
            box-shadow: var(--shadow-xl);
            padding: 2.5rem;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background-color: #e0e7ff;
            color: var(--primary);
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        .auth-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e1b4b;
            margin-bottom: 0.5rem;
        }

        .auth-subtitle {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .alert {
            padding: 0.875rem 1rem;
            border-radius: var(--radius-lg);
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-danger {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.375rem;
        }

        input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        .error-text {
            color: var(--danger);
            font-size: 0.8rem;
            margin-top: 0.35rem;
        }

        .btn-submit {
            width: 100%;
            padding: 0.875rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-lg);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
            margin-top: 0.5rem;
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
        }

        .btn-submit:active {
            transform: scale(0.99);
        }

        .auth-footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.875rem;
            color: var(--text-muted);
            border-top: 1px solid #f1f5f9;
            padding-top: 1.25rem;
        }

        .auth-footer a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 1.25rem;
            font-size: 0.85rem;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <a href="<?= site_url('student/login') ?>" class="back-link">&larr; 返回登入頁</a>

        <div class="auth-header">
            <span class="badge">Password Reset</span>
            <h1 class="auth-title">忘記密碼</h1>
            <p class="auth-subtitle">請輸入您註冊的電子郵件，系統將寄送驗證碼給您。</p>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('student/forgot-password') ?>" method="post" id="forgotPasswordForm">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="email">電子郵件 (Email)</label>
                <input type="email" id="email" name="email" placeholder="請輸入註冊時的電子郵件" required autofocus>
                <?php if (isset($error)): ?>
                    <div class="error-text"><?= $error ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit" id="forgotPasswordSubmit">發送驗證碼</button>
        </form>

        <div class="auth-footer">
            想起來了？ <a href="<?= site_url('student/login') ?>">返回登入</a>
        </div>
    </div>

    <script>
        // 防止重複點擊送出：快速點兩下會各自產生不同的驗證碼，
        // 導致畫面顯示的驗證碼與資料庫最終保存的驗證碼不一致而驗證失敗。
        document.getElementById('forgotPasswordForm').addEventListener('submit', function (e) {
            var btn = document.getElementById('forgotPasswordSubmit');
            if (btn.dataset.submitted === 'true') {
                e.preventDefault();
                return;
            }
            btn.dataset.submitted = 'true';
            btn.disabled = true;
            btn.textContent = '發送中...';
        });
    </script>
</body>
</html>
