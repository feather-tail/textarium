<?php
namespace App\Lib;

class PaginationHelper
{
  public static function render(int $page, int $perPage, int $total, array $query = []): string
  {
    if ($total <= $perPage) {
      return "";
    }

    $totalPages = (int) ceil($total / $perPage);
    $html = '<nav class="pagination" role="navigation" aria-label="Пагинация">';

    if ($page > 1) {
      $query["page"] = $page - 1;
      $html .= sprintf(
        '<a href="?%s" class="pagination__link is-prev" aria-label="Предыдущая страница">«</a>',
        htmlspecialchars(http_build_query($query), ENT_QUOTES | ENT_HTML5),
      );
    }

    for ($i = 1; $i <= $totalPages; $i++) {
      $query["page"] = $i;
      $html .= sprintf(
        '<a href="?%s" class="pagination__link%s">%d</a>',
        htmlspecialchars(http_build_query($query), ENT_QUOTES | ENT_HTML5),
        $i === $page ? " is-current" : "",
        $i,
      );
    }

    if ($page < $totalPages) {
      $query["page"] = $page + 1;
      $html .= sprintf(
        '<a href="?%s" class="pagination__link is-next" aria-label="Следующая страница">»</a>',
        htmlspecialchars(http_build_query($query), ENT_QUOTES | ENT_HTML5),
      );
    }

    return $html . "</nav>";
  }
}
