<?php

/**
 * Header template.
 *
 * @package          Flatsome\Templates
 * @flatsome-version 3.16.0
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="<?php flatsome_html_classes(); ?>">

<head>
	<meta charset="<?php bloginfo('charset'); ?>" />
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<?php do_action('flatsome_after_body_open'); ?>
	<?php wp_body_open(); ?>

	<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e('Skip to content', 'flatsome'); ?></a>

	<div id="wrapper">

		<?php do_action('flatsome_before_header'); ?>
		<?php $request_uri = trim($_SERVER['REQUEST_URI'], '/'); ?>
		<?php if ($request_uri === 'my' || strpos($request_uri, 'my/') === 0) : ?>
			<?php get_template_part('header', 'my');; ?>
		<?php else : ?>
			<header id="header" class="header <?php flatsome_header_classes(); ?>">
				<?php get_template_part('template-parts/header/header', 'wrapper'); ?>
			</header>
		<?php endif; ?>

		<?php do_action('flatsome_after_header'); ?>
		<?php
		$main_classes = flatsome_main_classes(false);
		if ($request_uri === 'my' || strpos($request_uri, 'my/') === 0) {
			$main_classes .= ' my-main';
		}
		?>

		<main id="main" class="<?php echo esc_attr(trim($main_classes)); ?>">