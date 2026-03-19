<?php
// /attendance_changes.php
require_once __DIR__ . "/app/auth.php";
require_login();

if (!is_admin()) {
  http_response_code(403);
  exit("Acesso negado.");
}

$title = "Histórico de mudanças";
$activeTab = "attendance";

function valid_date_ymd(string $date): bool {
  if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return false;
  [$y,$m,$d] = array_map('intval', explode('-', $date));
  return checkdate($m, $d, $y);
}

function br_date(string $ymd): string {
  if (!valid_date_ymd($ymd)) return $ymd;
  return date('d/m/Y', strtotime($ymd));
}

$date = (string)($_GET['date'] ?? date('Y-m-d'));
if (!valid_date_ymd($date)) $date = date('Y-m-d');

/**
 * Busca logs daquele dia + quantidade de mudanças
 */
$stmt = $pdo->prepare("
  SELECT
    al.id,
    al.created_at,
    u.name AS user_name,
    u.email,
    (SELECT COUNT(*) FROM attendance_log_items ali WHERE ali.log_id = al.id) AS changes_count
  FROM attendance_logs al
  LEFT JOIN users u ON u.id = al.user_id
  WHERE al.attendance_date = ?
  ORDER BY al.id DESC
");
$stmt->execute([$date]);
$logs = $stmt->fetchAll();

require_once __DIR__ . "/app/header.php";
?>

<div class="tab-content">
  <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:flex-start;">
    <h3 class="form-title" style="margin-bottom:10px;">
      Histórico de mudanças — <?= htmlspecialchars(br_date($date)) ?>
    </h3>

    <a class="btn" href="/attendance.php?date=<?= htmlspecialchars($date) ?>" style="background:#ddd;">
      Voltar
    </a>
  </div>

  <?php if (!$logs): ?>
    <div style="padding:10px 0;">Nenhum salvamento registrado para esta data.</div>
  <?php else: ?>

    <?php foreach ($logs as $log): ?>
      <div style="border:1px solid #eee;border-radius:10px;padding:14px;margin:12px 0;background:#fff;">
        <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;">
          <div>
            <div style="font-weight:800;">
              <?= htmlspecialchars(date('d/m/Y H:i', strtotime($log['created_at']))) ?>
              — <?= htmlspecialchars($log['user_name'] ?? 'Usuário removido') ?>
              <?= !empty($log['email']) ? '(' . htmlspecialchars($log['email']) . ')' : '' ?>
            </div>
            <div style="margin-top:4px;color:#555;">
              Mudanças: <strong><?= (int)$log['changes_count'] ?></strong>
            </div>
          </div>
        </div>

        <?php
          $stmtItems = $pdo->prepare("
            SELECT
              ali.old_status,
              ali.new_status,
              p.name AS person_name
            FROM attendance_log_items ali
            JOIN people p ON p.id = ali.person_id
            WHERE ali.log_id = ?
            ORDER BY p.name
          ");
          $stmtItems->execute([(int)$log['id']]);
          $items = $stmtItems->fetchAll();
        ?>

        <?php if (!$items): ?>
          <div style="margin-top:10px;color:#666;">
            Nenhuma alteração detectada (salvou sem mudar nada).
          </div>
        <?php else: ?>
          <div style="margin-top:10px;">
            <table style="width:100%;border-collapse:collapse;">
              <thead>
                <tr>
                  <th style="text-align:left;padding:8px;border-bottom:1px solid #eee;">Pessoa</th>
                  <th style="text-align:left;padding:8px;border-bottom:1px solid #eee;">Antes</th>
                  <th style="text-align:left;padding:8px;border-bottom:1px solid #eee;">Depois</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($items as $it):
                  $old = $it['old_status'] === 'present' ? 'Presente' : ($it['old_status'] === 'absent' ? 'Ausente' : '—');
                  $new = $it['new_status'] === 'present' ? 'Presente' : 'Ausente';
                ?>
                  <tr>
                    <td style="padding:8px;border-bottom:1px solid #f2f2f2;"><?= htmlspecialchars($it['person_name']) ?></td>
                    <td style="padding:8px;border-bottom:1px solid #f2f2f2;"><?= htmlspecialchars($old) ?></td>
                    <td style="padding:8px;border-bottom:1px solid #f2f2f2;"><?= htmlspecialchars($new) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

  <?php endif; ?>
</div>

<?php require_once __DIR__ . "/app/footer.php"; ?>