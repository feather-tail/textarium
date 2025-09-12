<?php include __DIR__ . '/partials/back_button.php'; ?>

<article id="page-article" class="full-article article">

  <h2 class="article__title"><?= htmlspecialchars($article['title']) ?></h2>

  <div class="article__meta">
    <span class="meta-label">
      <i class="fa-solid fa-calendar" aria-hidden="true"></i> Дата публикации:
    </span>
    <time class="article__date" datetime="<?= htmlspecialchars($article['created_at']) ?>">
      <?= htmlspecialchars($article['created_at']) ?>
    </time>
  </div>

  <?php if (!empty($categories)): ?>
    <div class="article__meta">
      <span class="meta-label">
        <i class="fa-solid fa-folder" aria-hidden="true"></i> Категории:
      </span>
      <?php foreach ($categories as $cat): ?>
        <a
          href="/?category[]=<?= $cat['id'] ?>"
          class="tag tag-category tag--link"
          title="Показать статьи из категории «<?= htmlspecialchars($cat['name']) ?>»"
        ><?= htmlspecialchars($cat['name']) ?></a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($tags)): ?>
    <div class="article__meta">
      <span class="meta-label">
        <i class="fa-solid fa-hashtag" aria-hidden="true"></i> Теги:
      </span>
      <?php foreach ($tags as $tag): ?>
        <a
          href="/?tag[]=<?= $tag['id'] ?>"
          class="tag tag-hash tag--link"
          title="Показать статьи с тегом «<?= htmlspecialchars($tag['name']) ?>»"
        >#<?= htmlspecialchars($tag['name']) ?></a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="article__content"><?= $article['content_html'] ?></div>

  <?php
    $user = \App\Lib\Auth::currentUser();
    $userRoles = $user['roles'] ?? [];
    $isModerator = in_array('moderator', $userRoles, true);
    $isAdmin = in_array('admin', $userRoles, true);
    $isVerified = in_array('verified', $userRoles, true);
    $isAuthor = isset($user['id']) && $user['id'] === $article['author_id'];
    $canEditOwn = $isAuthor && ($isVerified || $isModerator || $isAdmin)
        && in_array($article['status'], ['draft', 'pending'], true);

    $canEditAdmin = $isModerator || $isAdmin;
  ?>

  <?php ob_start(); ?>
    <form action="/article/<?= $article['id'] ?>/delete" method="POST" style="display:inline">
      <?= \App\Lib\Csrf::input() ?>
      <button type="submit" class="btn btn--icon btn--danger article__action" title="Удалить">
        <i class="fa-solid fa-trash"></i>
        <span class="visually-hidden">Удалить</span>
      </button>
    </form>
  <?php $deleteForm = ob_get_clean(); ?>

  <div class="article__actions">
    <?php if ($canEditOwn): ?>
      <a href="/edit?id=<?= $article['id'] ?>" class="btn btn--icon article__action" title="Редактировать">
        <i class="fa-solid fa-pen"></i>
        <span class="visually-hidden">Редактировать</span>
      </a>
      <?= $deleteForm ?>
    <?php elseif ($canEditAdmin): ?>
      <a href="/admin/edit?id=<?= $article['id'] ?>" class="btn btn--icon article__action" title="Редактировать">
        <i class="fa-solid fa-pen"></i>
        <span class="visually-hidden">Редактировать</span>
      </a>
      <?= $deleteForm ?>
    <?php endif ?>
  </div>
</article>
