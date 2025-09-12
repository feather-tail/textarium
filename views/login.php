<?php include __DIR__ . '/partials/back_button.php'; ?>

<h2 class="page-title">Вход</h2>

<?php if (!empty($errors)): ?>
  <div class="form-errors" role="alert">
    <?php foreach ($errors as $e): ?>
      <p><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($e) ?></p>
    <?php endforeach ?>
  </div>
<?php endif; ?>

<form method="POST" class="auth-form">
  <?= \App\Lib\Csrf::input() ?>

  <label class="form-field">
    <span class="form-field__label">Логин</span>
    <input
      type="text"
      name="username"
      class="form-input"
      autocomplete="username"
      required
    >
  </label>

  <label class="form-field">
    <span class="form-field__label">Пароль</span>
    <input
      type="password"
      name="password"
      class="form-input"
      autocomplete="current-password"
      required
    >
  </label>

  <div class="form-actions">
    <button type="submit" class="btn">
      <i class="fa-solid fa-right-to-bracket"></i> Войти
    </button>
  </div>
</form>
