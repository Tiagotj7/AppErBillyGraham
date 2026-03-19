<?php
require_once __DIR__ . "/app/auth.php";
require_login();

$title = "Registro de Frequência";
$activeTab = "attendance";

$user = current_user();

/**
 * 1) Determina a data alvo:
 * - Se veio POST: usa a data do POST (prioridade)
 * - Senão: usa GET[date]
 * - Senão: hoje
 */
function valid_date_ymd(string $date): bool {
  if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return false;
  [$y,$m,$d] = array_map('intval', explode('-', $date));
  return checkdate($m, $d, $y);
}

$date = date('Y-m-d');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $posted = (string)($_POST['date'] ?? '');
  if (valid_date_ymd($posted)) $date = $posted;
} else {
  $get = (string)($_GET['date'] ?? '');
  if (valid_date_ymd($get)) $date = $get;
}

/* Carrega pessoas sempre */
$people = $pdo->query("SELECT * FROM people ORDER BY name")->fetchAll();

/* 2) SALVAR (POST) */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();

  // statuses: [person_id => present/absent]
  $statuses = $_POST['status'] ?? [];
  if (!is_array($statuses)) $statuses = [];

  $pdo->beginTransaction();
  try {
    // apaga registros do dia selecionado (não do dia atual!)
    $stmtDel = $pdo->prepare("DELETE FROM attendance WHERE attendance_date=?");
    $stmtDel->execute([$date]);

    $stmtIns = $pdo->prepare(
      "INSERT INTO attendance (attendance_date, person_id, status, recorded_by)
       VALUES (?,?,?,?)"
    );

    foreach ($people as $p) {
      $pid = (int)$p['id'];
      $st = ($statuses[$pid] ?? 'absent');
      $st = ($st === 'present') ? 'present' : 'absent';

      $stmtIns->execute([$date, $pid, $st, (int)$user['id']]);
    }

    $pdo->commit();
  } catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    exit("Erro ao salvar frequência. Tente novamente.");
  }

  // redireciona para a mesma data salva
  header("Location: /attendance.php?date=" . urlencode($date) . "&saved=1");
  exit;
}

/* 3) Carrega status da data selecionada */
$stmt = $pdo->prepare("SELECT person_id, status FROM attendance WHERE attendance_date=?");
$stmt->execute([$date]);
$map = [];
foreach ($stmt->fetchAll() as $r) {
  $map[(int)$r['person_id']] = $r['status'];
}

require_once __DIR__ . "/app/header.php";
?>

<div class="tab-content">
  <h3 class="form-title">Registro de Frequência</h3>

  <?php if (!empty($_GET['saved'])): ?>
    <div class="success-message">Frequência salva para <?= htmlspecialchars(date('d/m/Y', strtotime($date))) ?>.</div>
  <?php endif; ?>

  <!-- Seleção de data (GET) -->
  <form method="get" class="form-row">
    <div class="form-group">
      <label>Data</label>
      <input type="date" name="date" value="<?= htmlspecialchars($date) ?>">
    </div>
    <div class="form-group" style="align-self:end;">
      <button class="btn btn-primary" type="submit">Ir</button>
    </div>
  </form>

  <!-- Salvar frequência (POST) -->
  <form method="post">
    <?= csrf_input() ?>
    <!-- IMPORTANTE: hidden com a data atual da tela -->
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