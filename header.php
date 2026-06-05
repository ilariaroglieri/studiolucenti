 <!doctype html>
	<!--[if !IE]>
	<html class="no-js non-ie" <?php language_attributes(); ?>> <![endif]-->
	<!--[if IE 7 ]>
	<html class="no-js ie7" <?php language_attributes(); ?>> <![endif]-->
	<!--[if IE 8 ]>
	<html class="no-js ie8" <?php language_attributes(); ?>> <![endif]-->
	<!--[if IE 9 ]>
	<html class="no-js ie9" <?php language_attributes(); ?>> <![endif]-->
	<!--[if gt IE 9]><!-->
<html <?php language_attributes(); ?>> <!--<![endif]-->
	<head>

		<meta charset="<?php bloginfo( 'charset' ); ?>"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="<?= get_the_excerpt() ?>">
		
		<title><?php bloginfo( 'name' ); ?><?php wp_title( '—', true, 'left' ); ?></title>

		<link rel="profile" href="http://gmpg.org/xfn/11"/>
		<?php wp_head(); ?>
	
	</head>

	<body <?php body_class(); ?>>
		<header role="banner" class="p-fixed">
			<div id="inner-header">
				<div id="logo">
					<h2 id="site-name" class="s-big">
						<a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?>
						</a>
					</h2>
				</div>

				<button aria-label="Main-Menu" class="menu-toggle d-none">
					<span></span>
					<span></span>
					<span></span>
				</button>
			</div>
			<?php wp_nav_menu( array( 'theme_location' => 'header-menu' ) ); ?>
		</header>
