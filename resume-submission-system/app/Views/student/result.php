<!DOCTYPE html><html lang="zh-TW"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>我的分發結果</title>
<style>body{margin:0;background:#f8fafc;color:#0f172a;font-family:Inter,"Noto Sans TC",sans-serif}.nav{background:#fff;border-bottom:1px solid #e2e8f0;padding:16px}.nav div,.shell{max-width:900px;margin:auto}.nav a{color:#4338ca;text-decoration:none;margin-right:20px}.shell{padding:40px 20px}.card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;box-shadow:0 8px 24px #0f172a0d}.result{border-left:5px solid #4f46e5;padding:16px 20px;background:#eef2ff;border-radius:8px;margin:22px 0}.result.unassigned{border-color:#f59e0b;background:#fffbeb}.school{font-size:1.6rem;font-weight:800;color:#312e81}.meta{color:#64748b}.choices{padding-left:24px;line-height:2}.rank{font-weight:700;color:#4338ca}</style></head><body>
<nav class="nav"><div><a href="/student/dashboard">學生首頁</a><a href="/student/preferences">我的志願序</a><strong>分發結果</strong></div></nav><main class="shell"><div class="card"><h1>我的分發結果</h1>
<?php if (!$run): ?><div class="result"><h2>結果尚未公布</h2><p>分發結果尚未正式發布，請留意最新公告。</p></div>
<?php elseif (!$result): ?><div class="result unassigned"><h2>本批次無您的分發資料</h2><p>可能是志願尚未正式送出或評分尚未確認，請洽管理員。</p></div>
<?php elseif ($result['result_status']==='admitted'): ?><div class="result"><p>恭喜錄取</p><div class="school"><?= esc($result['university_name_snapshot']) ?></div><p class="rank">第 <?= (int)$result['preference_rank'] ?> 志願</p></div>
<?php else: ?><div class="result unassigned"><h2>本次未分發</h2><p><?= esc($result['reason']) ?></p></div><?php endif; ?>
<?php if ($run && $result): ?><p class="meta">個人總分：<?= esc($result['score_snapshot']) ?>　結果發布時間：<?= esc($run['published_at']) ?></p><?php endif; ?>
<?php if ($choices): ?><h2>您送出的志願序</h2><ol class="choices"><?php foreach($choices as $choice): ?><li><?= esc($choice) ?></li><?php endforeach; ?></ol><?php endif; ?>
</div></main></body></html>
