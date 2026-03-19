<?php
// /attendance.php
require_once __DIR__ . "/app/auth.php";
require_login();

$title = "Registro de Frequência";
$activeTab = "attendance";

$user = current_user();

function valid_date_ymd(string $date): bool {
  if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return false;
  [$y,$m,$d] = array_map('intval', explode('-', $date));
  return checkdate($m, $d, $y);
}

function br_date(string $ymd): string {
  if (!valid_date_ymd($ymd)) return $ymd;
  return date('d/m/Y', strtotime($ymd));
}

/**
 * Data alvo SEMPRE em Y-m-d
 * Prioridade: POST > GET > hoje
 */
$date = date('Y-m-d');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();
  $posted = (string)($_POST['date'] ?? '');
  if (valid_date_ymd($posted)) $date = $posted;
} else {
  $get = (string)($_GET['date'] ?? '');
  if (valid_date_ymd($get)) $date = $get;
}

/* Carrega pessoas */
$people = $pdo->query("SELECT * FROM people ORDER BY name")->fetchAll();

/* =========================
   SALVAR FREQUÊNCIA (POST)
   ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $statuses = $_POST['status'] ?? [];
  if (!is_array($statuses)) $statuses = [];

  // 1) Carrega status antigo do dia (para comparar)
  $stmtOld = $pdo->prepare("SELECT person_id, status FROM attendance WHERE attendance_date=?");
  $stmtOld->execute([$date]);
  $oldMap = [];
  foreach ($stmtOld->fetchAll() as $r) {
    $oldMap[(int)$r['person_id']] = $r['status'];
  }

  $pdo->beginTransaction();
  try {
    // 2) Apaga registros do dia selecionado
    $stmtDel = $pdo->prepare("DELETE FROM attendance WHERE attendance_date=?");
    $stmtDel->execute([$date]);

    // 3) Reinsere registros do dia selecionado
    $stmtIns = $pdo->prepare(
      "INSERT INTO attendance (attendance_date, person_id, status, recorded_by)
       VALUES (?,?,?,?)"
    );

    // Vamos montar o "novo mapa" também
    $newMap = [];

    foreach ($people as $p) {
      $pid = (int)$p['id'];
      $st = ($statuses[$pid] ?? 'absent');
      $st = ($st === 'present') ? 'present' : 'absent';
      $newMap[$pid] = $st;

      $stmtIns->execute([$date, $pid, $st, (int)$user['id']]);
    }

    // 4) Cria um log de salvamento (sempre)
    $stmtLog = $pdo->prepare(
      "INSERT INTO attendance_logs (attendance_date, user_id, action) VALUES (?,?, 'save')"
    );
    $stmtLog->execute([$date, (int)$user['id']]);
    $logId = (int)$pdo->lastInsertId();

    // 5) Salva itens do log SOMENTE para mudanças
    $stmtItem = $pdo->prepare(
      "INSERT INTO attendance_log_items (log_id, person_id, old_status, new_status)
       VALUES (?,?,?,?)"
    );

    foreach ($newMap as $pid => $newStatus) {
      $oldStatus = $oldMap[$pid] ?? null; // null se não existia antes
      if ($oldStatus !== $newStatus) {
        $stmtItem->execute([$logId, (int)$pid, $oldStatus, $newStatus]);
      }
    }

    $pdo->commit();
  } catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    exit("Erro ao salvar frequência. Tente novamente.");
  }

  header("Location: /attendance.php?date=" . urlencode($date) . "&saved=1");
  exit;
}

/* =========================
   CARREGA STATUS DO DIA (GET)
   ========================= */
$stmt = $pdo->prepare("SELECT person_id, status FROM attendance WHERE attendance_date=?");
$stmt->execute([$date]);
$map = [];
foreach ($stmt->fetchAll() as $r) {
  $map[(int)$r['person_id']] = $r['status'];
}

/* =========================
   ÚLTIMA ALTERAÇÃO (SÓ ADMIN)
   ========================= */
$lastLog = null;
if (is_admin()) {
  $stmt = $pdo->prepare("
    SELECT al.id, al.created_at, u.name AS user_name, u.email
    FROM attendance_logs al
    LEFT JOIN users u ON u.id = al.user_id
    WHERE al.attendance_date = ?
    ORDER BY al.id DESC
    LIMIT 1
  ");
  $stmt->execute([$date]);
  $lastLog = $stmt->fetch() ?: null;
}

require_once __DIR__ . "/app/header.php";
?>

<div class="tab-content">
  <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
    <h3 class="form-title" style="margin-bottom:10px;">Registro de Frequência</h3>

    <?php if (is_admin()): ?>
      <a class="btn btn-primary" href="/attendance_changes.php?date=<?= htmlspecialchars($date) ?>" style="padding:10px 14px;">
        Histórico de mudanças
      </a>
    <?php endif; ?>
  </div>

  <?php if (!empty($_GET['saved'])): ?>
    <div class="success-message">
      Frequência salva para <?= htmlspecialchars(br_date($date)) ?>.
    </div>
  <?php endif; ?>

  <?php if (is_admin() && $lastLog): ?>
    <div style="margin: 10px 0; font-size: 14px; color:#444;">
      <strong>Última alteração:</strong>
      <?= htmlspecialchars(date('d/m/Y H:i', strtotime($lastLog['created_at']))) ?>
      por <?= htmlspecialchars($lastLog['user_name'] ?? 'Usuário removido') ?>
      <?= !empty($lastLog['email']) ? '(' . htmlspecialchars($lastLog['email']) . ')' : '' ?>
    </div>
  <?php endif; ?>

  <!-- GET: escolher data -->
  <form method="get" class="form-row">
    <div class="form-group">
      <label>Data</label>
      <input type="date" name="date" value="<?= htmlspecialchars($date) ?>">
    </div>
    <div class="form-group" style="align-self:end;">
      <button class="btn btn-primary" type="submit">Ir</button>
    </div>
  </form>

  <!-- POST: salvar -->
  <form method="post">
    <?= csrf_input() ?>
    <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">

    <div class="attendance-list">
      <?php if (!$people): ?>
        <div style="padding:10px 0;">Nenhuma pessoa cadastrada.</div>
      <?php else: ?>
        <?php foreach ($people as $p):
          $pid = (int)$p['id'];
          $st = $map[$pid] ?? 'absent';
        ?>
          <div class="attendance-item">
            <div class="person-name"><?= htmlspecialchars($p['name']) ?></div>

            <div class="attendance-status">
              <label>
                <input type="radio" name="status[<?= $pid ?>]" value="present" <?= $st === 'present' ? 'checked' : '' ?>>
                Presente
              </label>

              <label style="margin-left: 15px;">
                <input type="radio" name="status[<?= $pid ?>]" value="absent" <?= $st === 'absent' ? 'checked' : '' ?>>
                Ausente
              </label>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div style="margin-top: 20px; text-align: right;">
      <button class="btn btn-primary" type="submit">Salvar</button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . "/app/footer.php"; ?>