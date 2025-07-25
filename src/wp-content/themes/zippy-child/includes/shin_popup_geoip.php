<?php
add_action('rest_api_init', function () {
    register_rest_route('geoip/v1', '/info', [
        'methods'  => 'GET',
        'callback' => 'get_geoip_info',
        'permission_callback' => '__return_true',
    ]);
});

function get_client_ip()
{
    $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    foreach ($ip_keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip_list = explode(',', $_SERVER[$key]);
            return trim(end($ip_list));
        }
    }
    return 'UNKNOWN';
}

function get_geoip_info()
{
    $api_key = 'f662ff11785348448f213e61332b2dab';
    $client_ip = get_client_ip();

    $url = "https://api.geoapify.com/v1/ipinfo?apiKey=$api_key&ip=$client_ip";
    $response = wp_remote_get($url, ['timeout' => 5]);

    if (is_wp_error($response)) {
        return new WP_Error('geoip_error', 'Cannot fetch GeoIP info', ['status' => 500]);
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    return [
        'ip' => $client_ip,
        'country' => [
            'iso_code' => $data['country']['iso_code'] ?? '',
            'name' => $data['country']['name'] ?? '',
        ]
    ];
}

function geoip_redirect_popup_shortcode()
{
    ob_start();
?>
    <div class="redirect-popup" style="display:none;">
        <div class="overlay"></div>
        <?php echo do_shortcode('[block id="geoip-popup"]'); ?>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('geoip_popup', 'geoip_redirect_popup_shortcode');

add_action('wp_footer', function () {
    echo do_shortcode('[geoip_popup]');
?>
    <script>
        jQuery(document).ready(function($) {
            function setCookie(name, value, days) {
                const expires = new Date();
                expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
                document.cookie = name + "=" + value + ";expires=" + expires.toUTCString() + ";path=/";
            }

            function getCookie(name) {
                const nameEQ = name + "=";
                const ca = document.cookie.split(';');
                for (let i = 0; i < ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) == ' ') c = c.substring(1);
                    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length);
                }
                return null;
            }

            fetch("/wp-json/geoip/v1/info")
                .then((response) => response.json())
                .then((data) => {
                    const countryCode = data.country?.iso_code;
                    const countryName = data.country?.name;
                    const currentUrl = window.location.href;
                    console.log("Country:", countryCode, countryName);

                    const popup = $(".redirect-popup");
                    const userLocation = $(".user-location");
                    const countdown = $(".countdown");
                    const redirectNow = $(".redirect-now");
                    const stayHere = $(".stay-here");

                    let redirectUrl = "";
                    let cookieKey = "";

                    if (countryCode === "SG" && !currentUrl.includes("epos.com.sg")) {
                        redirectUrl = "https://www.epos.com.sg/";
                        cookieKey = "geoip_redirect_sg";
                    } else if (countryCode === "MY" && !currentUrl.includes("/my")) {
                        redirectUrl = "/my";
                        cookieKey = "geoip_redirect_my";
                    }

                    if (!redirectUrl || getCookie(cookieKey)) {
                        return;
                    }

                    if (cookieKey === "geoip_redirect_sg" && getCookie("geoip_redirect_my")) {
                        setCookie("geoip_redirect_my", "", -1);
                    } else if (cookieKey === "geoip_redirect_my" && getCookie("geoip_redirect_sg")) {
                        setCookie("geoip_redirect_sg", "", -1);
                    }

                    userLocation.text(countryName);
                    popup.fadeIn();

                    let secondsLeft = 5;
                    countdown.text(secondsLeft);

                    const timer = setInterval(function() {
                        secondsLeft--;
                        countdown.text(secondsLeft);

                        if (secondsLeft <= 0) {
                            clearInterval(timer);
                            setCookie(cookieKey, "1", 1);
                            window.location.href = redirectUrl;
                        }
                    }, 1000);

                    redirectNow.on("click", function(e) {
                        e.preventDefault();
                        clearInterval(timer);
                        setCookie(cookieKey, "1", 1);
                        window.location.href = redirectUrl;
                    });

                    stayHere.on("click", function(e) {
                        e.preventDefault();
                        clearInterval(timer);
                        setCookie(cookieKey, "1", 1);
                        popup.fadeOut();
                    });
                })
                .catch((error) => {
                    console.error("Error fetching location data:", error);
                });
        });
    </script>
<?php
});
