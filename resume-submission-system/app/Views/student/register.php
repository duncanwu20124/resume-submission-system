<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>創建學生帳號 | 學生履歷繳交系統</title>
    
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
            padding: 2rem 1.5rem;
            color: var(--text-main);
        }

        .auth-card {
            background: var(--surface);
            max-width: 480px;
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
            margin-top: 0.75rem;
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
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
            <span class="badge">Student Registration</span>
            <h1 class="auth-title">創建學生帳號</h1>
            <p class="auth-subtitle">請填寫以下資訊建立您的專屬學生帳號</p>
        </div>

        <form action="<?= site_url('student/register') ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="student_id">學號 (Student ID)</label>
                <input type="text" id="student_id" name="student_id" value="<?= old('student_id') ?>" placeholder="請輸入學號 (如: S112001)" required>
                <?php if (isset($validation) && $validation->hasError('student_id')): ?>
                    <div class="error-text"><?= $validation->getError('student_id') ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="name">姓名 (Full Name)</label>
                <input type="text" id="name" name="name" value="<?= old('name') ?>" placeholder="請輸入真實姓名" required>
                <?php if (isset($validation) && $validation->hasError('name')): ?>
                    <div class="error-text"><?= $validation->getError('name') ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">電子郵件 (Email Address)</label>
                <input type="email" id="email" name="email" value="<?= old('email') ?>" placeholder="student@example.com" required>
                <?php if (isset($validation) && $validation->hasError('email')): ?>
                    <div class="error-text"><?= $validation->getError('email') ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">設定密碼 (Password)</label>
                <input type="password" id="password" name="password" placeholder="密碼需至少 6 個字元" required>
                <?php if (isset($validation) && $validation->hasError('password')): ?>
                    <div class="error-text"><?= $validation->getError('password') ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password_confirm">確認密碼 (Confirm Password)</label>
                <input type="password" id="password_confirm" name="password_confirm" placeholder="請再次輸入密碼" required>
                <?php if (isset($validation) && $validation->hasError('password_confirm')): ?>
                    <div class="error-text"><?= $validation->getError('password_confirm') ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit">確認註冊 (Register)</button>
        </form>

        <div class="auth-footer">
            已有帳號？ <a href="<?= site_url('student/login') ?>">直接登入 (Login)</a>
        </div>
    </div>
</body>
</html>
