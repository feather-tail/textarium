<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?? "Библиотека" ?></title>

  <link rel="stylesheet" href="/assets/style.css">
  <link rel="stylesheet" href="/assets/editor.css">

  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer"
  />
</head>

<body id="site-body" class="site" data-theme="light">
  <header id="site-header" class="site-header" role="banner">
    <div class="site-header__inner container">
      <?php
      $user = \App\Lib\Auth::currentUser();
      $userRoles = $user["roles"] ?? [];
      $isLoggedIn = \App\Lib\Auth::isLoggedIn();
      ?>

      <nav
        id="global-nav"
        class="site-header__nav nav--global"
        role="navigation"
        aria-label="Основное меню"
      >
        <?php include __DIR__ . "/partials/nav_global.php"; ?>
      </nav>

      <nav
        id="user-bar"
        class="site-header__user user-bar"
        aria-label="Панель пользователя"
      >
        <?php if ($isLoggedIn): ?>
          <div class="user-bar__info">
            <i class="fa-solid fa-user"></i>
            <?= htmlspecialchars($user["username"]) ?>
            <span class="user-bar__roles">
              (<?= htmlspecialchars(implode(", ", $userRoles)) ?>)
            </span>
          </div>
          <a href="/logout" class="btn btn--icon user-bar__action" title="Выйти">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span class="visually-hidden">Выйти</span>
          </a>
        <?php else: ?>
          <a href="/login" class="btn btn--icon user-bar__action" title="Войти">
            <i class="fa-solid fa-right-to-bracket"></i>
            <span class="visually-hidden">Войти</span>
          </a>
        <?php endif; ?>
      </nav>

      <button
        id="theme-toggle"
        class="theme-toggle"
        aria-label="Переключить тему"
        aria-pressed="false"
      >
        <i class="fa-solid fa-circle-half-stroke"></i>
      </button>
    </div>
  </header>

  <main id="main-content" class="site-content container" role="main">
    <div id="flash-container" class="flash-container" aria-live="polite">
      <?php foreach (\App\Lib\Flash::getMessages() as $type => $msgs): ?>
        <?php foreach ($msgs as $msg): ?>
          <?php $icon = match ($type) {
            "success" => "fa-circle-check",
            "error" => "fa-circle-exclamation",
            "info" => "fa-circle-info",
            default => "fa-circle-info",
          }; ?>
          <div class="flash flash--<?= $type ?>" role="alert">
            <i class="fa-solid <?= $icon ?>"></i>
            <span class="flash__message"><?= htmlspecialchars($msg) ?></span>
          </div>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>

    <section id="page-content" class="page-content">
      <?= $content ?? "" ?>
    </section>
  </main>

  <footer id="site-footer" class="site-footer">
    <div class="site-footer__inner container">
      <p class="footer-copy">&copy; <?= date("Y") ?> Библиотека</p>
    </div>
  </footer>

  <script src="/assets/article-filters.js" defer></script>
  <script src="/assets/char-counter.js" defer></script>
  <script src="/assets/script.js" defer></script>
</body>
</html>
