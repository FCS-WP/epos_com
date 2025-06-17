<?php
function slugify($string)
{
  // Convert the string to lowercase
  $string = strtolower($string);

  // Replace spaces and special characters with dashes
  $string = preg_replace('/[^a-z0-9]+/', '_', $string);

  // Remove leading and trailing dashes
  $string = trim($string, '_');

  return $string;
}

function pr($data)
{
  echo '<style>
  #debug_wrapper {
    position: fixed;
    top: 0px;
    left: 0px;
    z-index: 999;
    background: #fff;
    color: #000;
    overflow: auto;
    width: 100%;
    height: 100%;
  }</style>';
    echo '<div id="debug_wrapper"><pre>';

    print_r($data); // or var_dump($data);
    echo "</pre></div>";
    die;

}


// Hook to initialize the custom endpoint
add_action('init', 'register_health_check_endpoint');

function register_health_check_endpoint() {
    add_rewrite_rule('^health-check/?$', 'index.php?health_check=1', 'top');
}

// Hook to handle the custom query variable
add_filter('query_vars', 'add_health_check_query_var');

function add_health_check_query_var($vars) {
    $vars[] = 'health_check';
    return $vars;
}

// Hook to handle the request
add_action('template_redirect', 'handle_health_check_endpoint');

function handle_health_check_endpoint() {
    global $wp_query;

    if (isset($wp_query->query_vars['health_check']) && $wp_query->query_vars['health_check'] == 1) {
        header('Content-Type: application/json');
        $response = array(
            'status' => 'ok',
            'timestamp' => current_time('mysql')
        );
        echo json_encode($response);
        exit;
    }
}
