<?php
require_once __DIR__ . "/app/auth.php";
require_login();

$title = "Histórico";
$activeTab = "history";
require_once __DIR__ . "/app/header.php";

$people = $pdo->query("SELECT id,name FROM people ORDER BY name")->fetchAll();
$person_id = (int)($_GET['person_id'] ?? 0);

$records = [];
if ($person_id) {
  $stmt = $pdo->prepare("
    SELECT attendance_date, status
    FROM attendance
    WHERE person_id=?
    ORDER BY attendance_date DESC
  ");
  $stmt->execute([$person_id]);
  $records = $stmt->fetchAll();
}

require_once __DIR__ . "/app/header.php";
?>

<div class="tab-content">
  <h3 class="form-title">Histórico de Frequência</h3>

  <form method="get" class="form-group">
    <label>Selecione uma pessoa</label>
    <select name="person_id" data-autosubmit="1">
      <option value="">Selecione</option>
      <?php foreach ($people as $p): ?>
        <option value="<?= (int)$p['id'] ?>" <?= $person_id === (int)$p['id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($p['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </form>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Data</th>
          <th>Status</th>
          <th>Ação</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$person_id): ?>
          <tr><td colspan="3" style="text-align:center;">Selecione uma pessoa para ver o histórico</td></tr>
        <?php elseif (!$records): ?>
          <tr><td colspan="3" style="text-align:center;">Nenhum registro encontrado</td></tr>
        <?php else: ?>
          <?php foreach ($records as $r):
            $st = $r['status'] === 'present' ? 'Presente' : 'Ausente';
            $color = $r['status'] === 'present' ? 'var(--success)' : 'var(--danger)';
            $d = date('d/m/Y', strtotime($r['attendance_date']));
          ?>
            <tr>
              <td><?= htmlspecialchars($d) ?></td>
              <td><span style="color:<?= $color ?>;font-weight:800;"><?= htmlspecialchars($st) ?></span></td>
              <td>
                <a class="btn action-btn edit-btn" href="/attendance.php?date=<?= htmlspecialchars($r['attendance_date']) ?>">
                  Editar
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . "/app/footer.php"; ?>