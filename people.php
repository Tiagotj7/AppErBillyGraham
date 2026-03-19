<?php
require_once __DIR__ . "/app/auth.php";
require_login();

$title = "Cadastro de Pessoas";
$activeTab = "people";

$user = current_user();
$can_edit = is_admin();
$error = "";

// CREATE/UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $can_edit) {
  $id = (int)($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $birthdate = $_POST['birthdate'] ?? '';
  $role = $_POST['role'] ?? '';

  if ($name !== '' && $birthdate !== '' && $role !== '') {
    if ($id > 0) {
      $stmt = $pdo->prepare("UPDATE people SET name=?, birthdate=?, role=? WHERE id=?");
      $stmt->execute([$name, $birthdate, $role, $id]);
    } else {
      $stmt = $pdo->prepare("INSERT INTO people (name,birthdate,role) VALUES (?,?,?)");
      $stmt->execute([$name, $birthdate, $role]);
    }
    header("Location: /people.php");
    exit;
  } else {
    $error = "Preencha todos os campos.";
  }
}

// DELETE
if (isset($_GET['delete']) && $can_edit) {
  $id = (int)$_GET['delete'];
  $stmt = $pdo->prepare("DELETE FROM people WHERE id=?");
  $stmt->execute([$id]);
  header("Location: /people.php");
  exit;
}

// LIST + SEARCH
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
  $stmt = $pdo->prepare("SELECT * FROM people WHERE name LIKE ? OR role LIKE ? ORDER BY name");
  $stmt->execute(["%$q%", "%$q%"]);
} else {
  $stmt = $pdo->query("SELECT * FROM people ORDER BY name");
}
$people = $stmt->fetchAll();

// EDIT load
$editPerson = null;
if (isset($_GET['edit']) && $can_edit) {
  $id = (int)$_GET['edit'];
  $stmt = $pdo->prepare("SELECT * FROM people WHERE id=?");
  $stmt->execute([$id]);
  $editPerson = $stmt->fetch() ?: null;
}

require_once __DIR__ . "/app/header.php";
?>

<div class="tab-content">
  <h3 class="form-title">
    <?= $can_edit ? ($editPerson ? "Editar Pessoa" : "Cadastrar Nova Pessoa") : "Pessoas Cadastradas" ?>
  </h3>

  <?php if ($error): ?>
    <div class="error-message"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($can_edit): ?>
    <form method="post" class="mb-20">
      <input type="hidden" name="id" value="<?= (int)($editPerson['id'] ?? 0) ?>">

      <div class="form-row">
        <div class="form-group">
          <label>Nome Completo</label>
          <input type="text" name="name" required value="<?= htmlspecialchars($editPerson['name'] ?? '') ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Data de Nascimento</label>
          <input type="date" name="birthdate" required value="<?= htmlspecialchars($editPerson['birthdate'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label>Posto</label>
          <select name="role" required>
            <?php
              $roles = ['escudeiro','arauto','sênior','emérito','conselheiro'];
              $sel = $editPerson['role'] ?? '';
              echo '<option value="">Selecione um posto</option>';
              foreach ($roles as $r) {
                $s = ($sel === $r) ? 'selected' : '';
                $label = mb_strtoupper(mb_substr($r, 0, 1)) . mb_substr($r, 1);
                echo "<option value=\"".htmlspecialchars($r)."\" $s>".htmlspecialchars($label)."</option>";
              }
            ?>
          </select>
        </div>
      </div>

      <button class="btn btn-primary"><?= $editPerson ? "Atualizar" : "Cadastrar" ?></button>
      <?php if ($editPerson): ?>
        <a class="btn" href="/people.php" style="margin-left:10px;background:#ddd;">Cancelar</a>
      <?php endif; ?>
    </form>
  <?php endif; ?>

  <h3 class="form-title" style="margin-top: 40px;">Pessoas</h3>

  <form class="search-bar" method="get">
    <input type="text" name="q" placeholder="Buscar por nome ou posto..." value="<?= htmlspecialchars($q) ?>">
    <button class="btn btn-primary">Buscar</button>
  </form>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Nome</th>
          <th>Data de Nascimento</th>
          <th>Posto</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$people): ?>
          <tr><td colspan="4" style="text-align:center;">Nenhuma pessoa cadastrada</td></tr>
        <?php else: ?>
          <?php foreach ($people as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['name']) ?></td>
              <td><?= htmlspecialchars(date('d/m/Y', strtotime($p['birthdate']))) ?></td>
              <td><?= htmlspecialchars(mb_strtoupper(mb_substr($p['role'], 0, 1)) . mb_substr($p['role'], 1)) ?></td>
              <td>
                <?php if ($can_edit): ?>
                  <a class="btn action-btn edit-btn" href="/people.php?edit=<?= (int)$p['id'] ?>">Editar</a>
                  <a class="btn action-btn delete-btn"
                     href="/people.php?delete=<?= (int)$p['id'] ?>"
                     data-confirm="Tem certeza que deseja excluir esta pessoa?">
                     Excluir
                  </a>
                <?php endif; ?>

                <a class="btn action-btn attendance-btn" href="/history.php?person_id=<?= (int)$p['id'] ?>">Frequência</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . "/app/footer.php"; ?>