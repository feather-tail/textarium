<?php
$user = \App\Lib\Auth::currentUser();
$userRoles = $user['roles'] ?? [];
$canChangePassword = array_intersect($userRoles, ['user', 'verified', 'moderator', 'admin']);
?>

<?php include __DIR__ . '/partials/back_button.php'; ?>

<h2 class="page-title"><div>Профиль </div><div class="page-title__username"><?= htmlspecialchars($user['username']) ?></div></h2>

<?php include __DIR__ . '/partials/nav_profile.php'; ?>

<h3>Сменить пароль</h3>

<form class="password-form" method="POST" action="/change-password">
  <?= \App\Lib\Csrf::input() ?>

  <label class="form-field">
    <span class="form-field__label">Текущий пароль</span>
    <input class="form-input" type="password" name="current_password" required>
  </label>

  <label class="form-field">
    <span class="form-field__label">Новый пароль</span>
    <input class="form-input" type="password" name="new_password" required>
  </label>

  <label class="form-field">
    <span class="form-field__label">Повторите новый пароль</span>
    <input class="form-input" type="password" name="confirm_password" required>
  </label>

  <button class="btn" type="submit">
    <i class="fa-solid fa-key"></i> Сменить пароль
  </button>
</form>