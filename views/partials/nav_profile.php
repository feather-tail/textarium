<?php
  $activeProfile = $activeProfile
               ?? strtok(ltrim($_SERVER['REQUEST_URI'], '/'), '/');

  $user = \App\Lib\Auth::currentUser();
  $userRoles = $user['roles'] ?? [];
  $canDrafts = array_intersect($userRoles, ['verified', 'moderator', 'admin']);
?>

<ul class="profile-nav__list" role="menu">
  <?php if (!empty($canDrafts)): ?>
    <li class="profile-nav__item" role="none">
      <a href="/my-articles"
         class="profile-nav__link<?= $activeProfile === 'my-articles' ? ' is-active' : '' ?>"
         role="menuitem"
      >
        <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
        <span>Мои статьи</span>
      </a>
    </li>

    <li class="profile-nav__item" role="none">
      <a href="/my-drafts"
         class="profile-nav__link<?= $activeProfile === 'my-drafts' ? ' is-active' : '' ?>"
         role="menuitem"
      >
        <i class="fa-solid fa-pencil" aria-hidden="true"></i>
        <span>Черновики</span>
      </a>
    </li>
  <?php endif; ?>

  <li class="profile-nav__item" role="none">
    <a href="/change-password"
      class="profile-nav__link<?= $activeProfile === 'change-password' ? ' is-active' : '' ?>"
      role="menuitem"
    >
      <i class="fa-solid fa-key" aria-hidden="true"></i>
      <span>Сменить пароль</span>
    </a>
  </li>
</ul>
