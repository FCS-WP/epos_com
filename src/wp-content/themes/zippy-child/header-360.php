<header id="header" class="header header-360 <?php flatsome_header_classes(); ?>">
    <div class="header-wrapper">
        <div id="masthead" class="header-main <?php header_inner_class('main'); ?>">
            <div class="header-inner flex-row container <?php flatsome_logo_position(); ?>" role="navigation">

                <!-- Logo -->
                <div id="logo-360" class="flex-col logo">
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                     <img width="250" height="72" src="/wp-content/uploads/2025/12/Logo-360.png" class="header_logo header-logo" alt="logo-epos-360">
                    </a>
                </div>

                <!-- Mobile Left Elements -->
                <div class="flex-col show-for-medium flex-left mobile-left d-none">
                    <ul class="mobile-nav nav nav-left <?php flatsome_nav_classes('main-mobile'); ?>">
                        <?php flatsome_header_elements('header_mobile_elements_left', 'mobile'); ?>
                    </ul>
                </div>

                <!-- Left Elements -->
                <div class="flex-col hide-for-medium flex-left
                <?php if (get_theme_mod('logo_position', 'left') == 'left') echo 'flex-grow'; ?>">
                    <!--  -->
                </div>

                <!-- Right Elements -->
                <div class="flex-col hide-for-medium flex-right">
                    <?php
                    if (class_exists('FlatsomeNavDropdown')) {
                        wp_nav_menu(array(
                            'theme_location' => '360_menu',
                            'container'      => false,
                            'menu_class'     => 'header-nav header-nav-main nav nav-left nav-line-bottom  ',
                            'depth'          => 3,
                            'walker'         => new FlatsomeNavDropdown(),
                            'fallback_cb'    => false,
                        ));
                    }
                    ?>
                </div>



                <!-- Mobile Right Elements -->
                <div class="flex-col show-for-medium flex-right">
                    <ul class="mobile-nav nav nav-right <?php flatsome_nav_classes('main-mobile'); ?>">

                    </ul>
                </div>

            </div>

            <?php if (get_theme_mod('header_divider', 1)) { ?>
                <div class="container">
                    <div class="top-divider full-width"></div>
                </div>
            <?php } ?>
        </div>
    </div>
</header>