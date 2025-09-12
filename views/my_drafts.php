<?php
  $user = \App\Lib\Auth::currentUser();
  $userRoles = $user['roles'] ?? [];
  $canAccessDrafts = array_intersect($userRoles, ['verified', 'moderator', 'admin']);
?>

<?php include __DIR__ . '/partials/back_button.php'; ?>

<?php if (empty($canAccessDrafts)): ?>
  <div class="no-access">
    <i class="fa-solid fa-circle-exclamation"></i>
    У вас нет доступа к черновикам. Только верифицированные пользователи могут создавать и хранить черновики.
  </div>
<?php else: ?>

<h2 class="page-title">Мои черновики</h2>

<?php include __DIR__ . '/partials/nav_profile.php'; ?>

<?php if (empty($drafts)): ?>
  <p class="no-results">
    <i class="fa-solid fa-circle-info"></i> У вас нет черновиков.
  </p>
<?php else: ?>
  <ul class="article-list">
    <?php foreach ($drafts as $a): ?>
      <?php ob_start(); ?>
        <form class="inline-form"
              action="/article/<?= $a['id'] ?>/delete"
              method="POST"
              onsubmit="return confirm('Удалить черновик?')">
          <?= \App\Lib\Csrf::input() ?>
          <button class="article-action"
                  type="submit"
                  title="Удалить">
            <i class="fa-solid fa-trash"></i>
          </button>
        </form>
      <?php $deleteForm = ob_get_clean(); ?>

      <?php ob_start(); ?>
        <form class="inline-form"
              action="/submit-draft"
              method="POST"
              onsubmit="return confirm('Отправить на модерацию?')">
          <?= \App\Lib\Csrf::input() ?>
          <input type="hidden" name="id" value="<?= $a['id'] ?>">
          <button class="article-action action--send"
                  type="submit"
                  title="Отправить на модерацию">
            <i class="fa-solid fa-paper-plane"></i>
          </button>
        </form>
      <?php $sendForm = ob_get_clean(); ?>

      <li class="article-card">

        <a class="article-card__title"
           href="/edit?id=<?= $a['id'] ?>"
           title="Редактировать">
          <?= htmlspecialchars($a['title']) ?>
        </a>

        <div class="article-card__meta">
          <span class="meta-label">
            <i class="fa-solid fa-calendar"></i> Создано:
          </span>
          <time datetime="<?= $a['created_at'] ?>">
            <?= htmlspecialchars($a['created_at']) ?>
          </time>
        </div>

        <?php if (!empty($allCategories[$a['id']])): ?>
          <div class="article-card__meta">
            <?php foreach ($allCategories[$a['id']] as $cat): ?>
              <span class="tag"><?= htmlspecialchars($cat['name']) ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($allTags[$a['id']])): ?>
          <div class="article-card__meta">
            <?php foreach ($allTags[$a['id']] as $tag): ?>
              <span class="tag">#<?= htmlspecialchars($tag['name']) ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="article-card__meta">
          <span class="status-badge status--draft">
            <i class="fa-solid fa-file-pen"></i> Черновик
          </span>
        </div>

        <div class="article__actions">
          <a class="article-action"
             href="/edit?id=<?= $a['id'] ?>"
             title="Редактировать">
            <i class="fa-solid fa-pen"></i>
          </a>
          <?= $deleteForm ?>
          <?= $sendForm ?>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>

  <?= \App\Lib\PaginationHelper::render($page, $perPage, $total, $_GET) ?>
<?php endif; ?>

<?php endif; ?>
