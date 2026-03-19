<?php
require_once __DIR__ . "/app/auth.php";
require_login();

$title = "Registro de Frequência";
$activeTab = "attendance";

$user = current_user();

// data selecionada
$date = $_GET['date'] ?? date('Y-m-d');

// lista pessoas
$people = $pdo->query("SELECT * FROM people ORDER BY name")->fetchAll();

// carregar status da data
$stmt = $pdo->prepare("SELECT person_id,status FROM attendance WHERE attendance_date=?");
$stmt->execute([$date]);
$map = [];
foreach ($stmt->fetchAll() as $r) {
  $map[(int)$r['person_id']] = $r['status'];
}

// salvar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $date = $_POST['date'] ?? $date;
  $statuses = $_POST['status'] ?? []; // [person_id => present/absent]

  $pdo->beginTransaction();

  // remove registros do dia e regrava tudo
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

  header("Location: /attendance.php?date=" . urlencode($date));
  exit;
}

require_once __DIR__ . "/app/header.php";
?>

<div class="tab-content">
  <h3 class="form-title">Registro de Frequência</h3>

  <form method="get" class="form-row">
    <div class="form-group">
      <label>Data</label>
      <input type="date" name="date" value="<?= htmlspecialchars($date) ?>">
    </div>
    <div class="form-group" style="align-self:end;">
      <button class="btn btn-primary">Ir</button>
    </div>
  </form>

  <form method="post">
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
      <button class="btn btn-primary">Salvar</button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . "/app/footer.php"; ?>