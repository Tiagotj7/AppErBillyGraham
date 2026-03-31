<?php
require_once __DIR__ . "/app/config.php"; // só para PDO e CSRF (não exige login)

$title = "Área do Aluno - Faltas";
$error = "";
$result = null;
$absences = [];
$totalAbsences = 0;

function normalize_name(string $s): string {
  $s = trim(mb_strtolower($s));
  $s = preg_replace('/\s+/', ' ', $s);
  return $s;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();

  $name = trim($_POST['name'] ?? '');
  $birthdate = trim($_POST['birthdate'] ?? '');

  if ($name === '' || $birthdate === '') {
    $error = "Informe seu nome e data de nascimento.";
  } else {
    // Procura pessoa pelo nome + nascimento (para evitar consulta aberta por nome apenas)
    $stmt = $pdo->prepare("
      SELECT id, name, role, birthdate
      FROM people
      WHERE birthdate = ?
    ");
    $stmt->execute([$birthdate]);
    $candidates = $stmt->fetchAll();

    // filtra por nome (case-insensitive)
    $found = null;
    $target = normalize_name($name);

    foreach ($candidates as $p) {
      if (normalize_name($p['name']) === $target) {
        $found = $p;
        break;
      }
    }

    if (!$found) {
      $error = "Não encontramos esse aluno com esses dados. Verifique e tente novamente.";
    } else {
      $result = $found;

      // Total de faltas
      $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM attendance
        WHERE person_id = ? AND status = 'absent'
      ");
      $stmt->execute([(int)$result['id']]);
      $totalAbsences = (int)($stmt->fetch()['total'] ?? 0);

      // Lista de datas das faltas (opcional)
      $stmt = $pdo->prepare("
        SELECT attendance_date
        FROM attendance
        WHERE person_id = ? AND status = 'absent'
        ORDER BY attendance_date DESC
      ");
      $stmt->execute([(int)$result['id']]);
      $absences = $stmt->fetchAll();
    }
  }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
  <div class="login-container" style="background:#f0f2f5;">
    <div class="login-form">
      <div class="login-logo">
        <img src="/assets/img/logo.svg" alt="Logo" class="login-logo-img">
      </div>

      <h2>Área do Aluno</h2>
      <p style="text-align:center;color:#555;margin-bottom:18px;">
        Consulte suas faltas informando seus dados.
      </p>

      <?php if ($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" autocomplete="off">
        <?= csrf_input() ?>

        <div class="form-group">
          <label>Nome completo</label>
          <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label>Data de nascimento</label>
          <input type="date" name="birthdate" required value="<?= htmlspecialchars($_POST['birthdate'] ?? '') ?>">
        </div>

        <button class="btn btn-primary btn-block" type="submit">Consultar</button>
      </form>

      <?php if ($result): ?>
        <div style="margin-top:20px;padding-top:16px;border-top:1px solid #eee;">
          <div style="font-weight:800;font-size:16px;"><?= htmlspecialchars($result['name']) ?></div>
          <div style="color:#555;margin-top:4px;">
            <strong>Posto:</strong> <?= htmlspecialchars(mb_strtoupper(mb_substr($result['role'],0,1)) . mb_substr($result['role'],1)) ?>
          </div>
          <div style="margin-top:10px;">
            <strong>Total de faltas:</strong>
            <span style="color:var(--danger);font-weight:900;"><?= (int)$totalAbsences ?></span>
          </div>

          <?php if (!empty($absences)): ?>
            <div style="margin-top:12px;">
              <div style="font-weight:800;margin-bottom:6px;">Datas das faltas</div>
              <div style="max-height:180px;overflow:auto;border:1px solid #eee;border-radius:8px;padding:10px;">
                <?php foreach ($absences as $a): ?>
                  <div style="padding:6px 0;border-bottom:1px solid #f2f2f2;">
                    <?= htmlspecialchars(date('d/m/Y', strtotime($a['attendance_date']))) ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <div style="margin-top:18px;text-align:center;">
        <a href="/index.php">Voltar para Login</a>
      </div>
    </div>
  </div>
</body>
</html>