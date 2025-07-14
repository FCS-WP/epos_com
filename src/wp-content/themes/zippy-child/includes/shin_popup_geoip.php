<?php

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
            // Cookie helpers
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

            fetch("https://api.geoapify.com/v1/ipinfo?apiKey=f662ff11785348448f213e61332b2dab")
                .then((response) => response.json())
                .then((data) => {
                    const countryCode = data.country.iso_code;
                    const countryName = data.country.name;
                    const currentUrl = window.location.href;
                    
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
                    if (cookieKey === "geoip_redirect_sg" && getCookie("geoip_redirect_my")) {
                        setCookie("geoip_redirect_my", "", -1);
                    } else if (cookieKey === "geoip_redirect_my" && getCookie("geoip_redirect_sg")) {
                        setCookie("geoip_redirect_sg", "", -1);
                    }
                    if (redirectUrl && !getCookie(cookieKey)) {
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
                    }
                })
                .catch((error) => {
                    console.error("Error fetching location data:", error);
                });
        });
    </script>
<?php
});
