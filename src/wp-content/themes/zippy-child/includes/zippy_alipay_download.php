<?php

// === Custom URL: /page/landing/download.html ===
add_action('init', function () {
  add_rewrite_rule(
    '^page/landing/download\.html$',
    'index.php?alipay-download=1',
    'top'
  );
});

add_filter('query_vars', function ($vars) {
  $vars[] = 'alipay-download';
  return $vars;
});

add_action('template_redirect', function () {
  if (get_query_var('alipay-download')) {
    include THEME_DIR  . '-child' .  '/alipay-dowload.php';
    exit;
  }
});
