<?php

/**
 * Template for /page/landing/download.html
 * 
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EPOS Download</title>

  <link rel="icon" href="<?php echo esc_url(get_site_icon_url()); ?>" type="image/png">

  <style>
    body {
      margin: 0;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
      background: #fff;
    }

    .iframe-wrapper {
      width: 100%;
      height: 100vh;
      border: none;
    }

    .fallback {
      text-align: center;
      padding: 1rem;
    }
  </style>
</head>

<body>
  <iframe
    src="https://ac.alipay.com/page/landing/download.html"
    class="iframe-wrapper"
    title="Alipay Download Page"
    loading="lazy"></iframe>

  <noscript class="fallback">
    If the page doesn’t load,
    <a href="https://ac.alipay.com/page/landing/download.html" target="_blank" rel="noopener noreferrer">
      click here to open in a new tab
    </a>.
  </noscript>
</body>

</html>
