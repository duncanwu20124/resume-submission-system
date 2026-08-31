<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>志願序確認單 | 學生甄選與志願媒合系統</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #cbd5e1;
            --background: #f1f5f9;
            --radius-lg: 0.75rem;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', system-ui, -apple-system, "PingFang TC", "Microsoft JhengHei", "Noto Sans TC", sans-serif;
            background: var(--background);
            color: var(--text-main);
            padding: 2rem 1rem;
        }

        .toolbar {
            max-width: 720px;
            margin: 0 auto 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .toolbar a {
            font-size: 0.875rem;
            color: var(--text-muted);
            text-decoration: none;
        }
        .toolbar a:hover { color: var(--text-main); }

        .btn-print {
            padding: 0.65rem 1.25rem;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: var(--radius-lg);
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .receipt {
            position: relative;
            max-width: 720px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2.5rem 3rem;
            overflow: hidden;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .watermark {
            position: absolute;
            inset: 0;
            display: flex;
            flex-wrap: wrap;
            align-content: space-around;
            justify-content: space-around;
            pointer-events: none;
            z-index: 0;
            opacity: 0.08;
            transform: rotate(-28deg) scale(1.3);
        }
        .watermark span {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1e1b4b;
            white-space: nowrap;
            margin: 1.2rem 1.8rem;
        }

        .receipt-content { position: relative; z-index: 1; }

        .receipt-header {
            text-align: center;
            border-bottom: 2px solid var(--text-main);
            padding-bottom: 1.25rem;
            margin-bottom: 1.5rem;
        }
        .receipt-header h1 { font-size: 1.4rem; font-weight: 700; margin-bottom: 0.35rem; }
        .receipt-header p { font-size: 0.85rem; color: var(--text-muted); }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem 1.5rem;
            margin-bottom: 1.75rem;
            font-size: 0.9rem;
        }
        .info-item { display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border); padding-bottom: 0.4rem; }
        .info-label { color: var(--text-muted); }
        .info-value { font-weight: 600; }

        .section-title { font-size: 1rem; font-weight: 700; margin-bottom: 0.85rem; }

        table.choice-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; margin-bottom: 1.75rem; }
        table.choice-table th, table.choice-table td {
            border: 1px solid var(--border); padding: 0.6rem 0.75rem; text-align: left;
        }
        table.choice-table th { background: var(--background); font-weight: 700; width: 90px; }

        .receipt-footer {
            font-size: 0.75rem; color: var(--text-muted); line-height: 1.7;
            border-top: 1px solid var(--border); padding-top: 1rem;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .receipt { border: none; padding: 0; max-width: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="<?= site_url('student/preferences') ?>">&larr; 返回志願序頁面</a>
        <button type="button" class="btn-print" onclick="window.print()">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-12 0h12v4H6v-4z"></path></svg>
            列印 / 下載 PDF
        </button>
    </div>

    <div class="receipt">
        <div class="watermark" aria-hidden="true">
            <?php for ($i = 0; $i < 24; $i++): ?>
                <span>系統存證 OFFICIAL</span>
            <?php endfor; ?>
        </div>

        <div class="receipt-content">
            <div class="receipt-header">
                <h1>志願序送出確認單</h1>
                <p>本確認單由系統於送出當下自動產生，為志願序送出之正式憑證</p>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">學號</span>
                    <span class="info-value"><?= esc($student['student_id']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">姓名</span>
                    <span class="info-value"><?= esc($student['name']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?= esc($student['email']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">送出時間戳記</span>
                    <span class="info-value"><?= esc($preference['submitted_at']) ?></span>
                </div>
            </div>

            <div class="section-title">最終志願序清單</div>
            <table class="choice-table">
                <thead>
                    <tr>
                        <th>順位</th>
                        <th>學校名稱</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($choices as $index => $choice): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= esc($choice) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="receipt-footer">
                此確認單內容根據系統資料庫紀錄自動產生，志願序一經送出即鎖定，不可修改。若對本確認單內容有任何疑義，請於分發結果公告前聯繫承辦單位確認；逾期恕不受理更正申請。<br>
                文件編號：PREF-<?= esc($student['student_id']) ?>-<?= esc(date('Ymd', strtotime($preference['submitted_at']))) ?>
            </div>
        </div>
    </div>
</body>
</html>
