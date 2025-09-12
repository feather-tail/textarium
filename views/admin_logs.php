<?php
  $user = \App\Lib\Auth::currentUser();
  $userRoles    = $user['roles'] ?? [];
  $isModerator  = in_array('moderator', $userRoles, true);
  $isAdmin      = in_array('admin',     $userRoles, true);
  $canViewLogs  = $isModerator || $isAdmin;
?>

<?php include __DIR__ . '/partials/back_button.php'; ?>

<h2 class="page-title">Журнал действий</h2>

<?php include __DIR__ . '/partials/nav_admin.php'; ?>

<?php if (!$canViewLogs): ?>
  <div class="no-access">
    <i class="fa-solid fa-circle-exclamation"></i>
    У вас нет доступа к просмотру журнала действий.
  </div>
<?php else: ?>

  <form method="GET" class="filter-form log-filters">
    <label class="filter-field">
      <span class="filter-label">Пользователь</span>
      <select name="user_id" class="filter-select">
        <option value="0">— Все —</option>
        <?php foreach ($users as $u): ?>
          <option value="<?= $u['id'] ?>"
                  <?= ((int)($_GET['user_id'] ?? 0) === (int)$u['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($u['username']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <label class="filter-field">
      <span class="filter-label">Действие</span>
      <select name="action" class="filter-select">
        <option value="">— Все —</option>
        <?php foreach ($actions as $act): ?>
          <option value="<?= htmlspecialchars($act) ?>"
                  <?= ($_GET['action'] ?? '') === $act ? 'selected' : '' ?>>
            <?= htmlspecialchars($act) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <label class="filter-field">
      <span class="filter-label">Дата от</span>
      <input type="date" name="from"
             value="<?= htmlspecialchars($_GET['from'] ?? '') ?>"
             class="filter-input">
    </label>

    <label class="filter-field">
      <span class="filter-label">до</span>
      <input type="date" name="to"
             value="<?= htmlspecialchars($_GET['to'] ?? '') ?>"
             class="filter-input">
    </label>

    <button type="submit" class="btn btn--icon" title="Найти">
      <i class="fa-solid fa-magnifying-glass"></i>
      <span class="visually-hidden">Найти</span>
    </button>
  </form>

  <div class="admin-grid admin-grid--cols-5">
    <div class="admin-grid__header">Дата</div>
    <div class="admin-grid__header">Пользователь</div>
    <div class="admin-grid__header">Действие</div>
    <div class="admin-grid__header">Объект</div>
    <div class="admin-grid__header">Детали</div>

    <?php foreach ($logs as $log): ?>
      <div class="admin-grid__row">
        <div class="admin-grid__cell" data-label="Дата">
          <?= htmlspecialchars($log['created_at']) ?>
        </div>
        <div class="admin-grid__cell" data-label="Пользователь">
          <?= htmlspecialchars($log['username'] ?? '—') ?>
        </div>
        <div class="admin-grid__cell" data-label="Действие">
          <?= htmlspecialchars($log['action']) ?>
        </div>
        <div class="admin-grid__cell" data-label="Объект">
          <?= htmlspecialchars($log['object_type']) ?> #<?= (int)$log['object_id'] ?>
        </div>
        <div class="admin-grid__cell details" data-label="Детали">
          <?= nl2br(htmlspecialchars($log['details'] ?? '')) ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?= \App\Lib\PaginationHelper::render($page, $perPage, $total, $_GET) ?>

<?php endif; ?>
