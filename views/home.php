<section id="page-home" class="page-home">

  <button
    class="filters-toggle btn btn--icon"
    aria-expanded="false"
    aria-controls="filters-panel"
  >
    <i class="fa-solid fa-filter"></i>
    <span class="visually-hidden">Фильтры</span>
  </button>

  <aside id="filters-panel" class="filters-panel" tabindex="-1">
    <h2 class="visually-hidden">Фильтры</h2>

    <form method="GET" id="article-filters" class="filters-form">
      <div class="filters-row">
        <input
          type="text"
          name="q"
          class="filter-input filter-search"
          placeholder="Поиск…"
          value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
          autocomplete="off"
        >
      </div>

      <fieldset class="filter-group filter-categories">
        <legend class="filter-legend">
          <i class="fa-solid fa-folder-open"></i> Категории
        </legend>
        <div class="filter-options">
          <?php foreach ($categories as $cat): ?>
            <label class="filter-checkbox">
              <input
                type="checkbox"
                name="category[]"
                value="<?= $cat['id'] ?>"
                <?= in_array($cat['id'], (array)($_GET['category'] ?? [])) ? 'checked' : '' ?>
              >
              <span><?= htmlspecialchars($cat['name']) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </fieldset>

      <fieldset class="filter-group filter-tags">
        <legend class="filter-legend">
          <i class="fa-solid fa-hashtag"></i> Теги
        </legend>
        <div class="filter-options">
          <?php foreach ($tags as $tag): ?>
            <label class="filter-checkbox">
              <input
                type="checkbox"
                name="tag[]"
                value="<?= $tag['id'] ?>"
                <?= in_array($tag['id'], (array)($_GET['tag'] ?? [])) ? 'checked' : '' ?>
              >
              <span>#<?= htmlspecialchars($tag['name']) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </fieldset>

      <div class="filters-row">
        <label class="filter-label">Сортировка:
          <select name="sort" class="filter-select">
            <option value="created_desc" <?= ($_GET['sort'] ?? '') === 'created_desc' ? 'selected' : '' ?>>
              Сначала новые
            </option>
            <option value="created_asc" <?= ($_GET['sort'] ?? '') === 'created_asc' ? 'selected' : '' ?>>
              Сначала старые
            </option>
            <option value="title_asc" <?= ($_GET['sort'] ?? '') === 'title_asc' ? 'selected' : '' ?>>
              По названию (А-Я)
            </option>
            <option value="title_desc" <?= ($_GET['sort'] ?? '') === 'title_desc' ? 'selected' : '' ?>>
              По названию (Я-А)
            </option>
          </select>
        </label>

        <button type="button" id="reset-filters" class="btn btn--secondary">
          <i class="fa-solid fa-arrows-rotate"></i>
          <span class="visually-hidden">Сбросить фильтры</span>
        </button>
      </div>
    </form>

    <p class="filter-count" id="article-count">
      Найдено статей: <?= $total ?>
    </p>
  </aside>

  <section id="article-results" class="articles-section">
    <h2 class="visually-hidden">Опубликованные статьи</h2>

    <?php if (empty($articles)): ?>
      <p class="no-results">
        <i class="fa-solid fa-circle-xmark"></i>
        По заданным фильтрам статьи не найдены.
      </p>
    <?php else: ?>
      <ul class="article-list">
        <?php foreach ($articles as $article): ?>
          <li class="article-card article-item">
            <a
              href="/article/<?= $article['id'] ?>-<?= urlencode($article['slug']) ?>"
              class="article-card__title"
            >
              <?= htmlspecialchars($article['title']) ?>
            </a>

            <div class="article-card__meta">
              <span class="meta-label">
                <i class="fa-solid fa-calendar"></i> Дата публикации:
              </span>
              <time datetime="<?= htmlspecialchars($article['created_at']) ?>">
                <?= htmlspecialchars($article['created_at']) ?>
              </time>
            </div>

            <?php if (!empty($allCategories[$article['id']])): ?>
              <div class="article-card__meta">
                <span class="meta-label">
                  <i class="fa-solid fa-folder"></i> Категории:
                </span>
                <?php foreach ($allCategories[$article['id']] as $c): ?>
                  <a
                    href="/?category[]=<?= $c['id'] ?>"
                    class="tag tag-category tag--link"
                    title="Показать статьи из категории «<?= htmlspecialchars($c['name']) ?>»"
                  ><?= htmlspecialchars($c['name']) ?></a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <?php if (!empty($allTags[$article['id']])): ?>
              <div class="article-card__meta">
                <span class="meta-label">
                  <i class="fa-solid fa-hashtag"></i> Тэги:
                </span>
                <?php foreach ($allTags[$article['id']] as $tag): ?>
                  <a
                    href="/?tag[]=<?= $tag['id'] ?>"
                    class="tag tag--link"
                    title="Показать статьи с тегом «<?= htmlspecialchars($tag['name']) ?>»"
                  >#<?= htmlspecialchars($tag['name']) ?></a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

          </li>
        <?php endforeach; ?>
      </ul>

      <?= \App\Lib\PaginationHelper::render($page, $perPage, $total, $_GET) ?>
    <?php endif; ?>
  </section>
</section>

<hr class="divider">
