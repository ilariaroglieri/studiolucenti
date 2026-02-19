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
<html> <!--<![endif]-->
	<head>

		<meta charset="<?php bloginfo( 'charset' ); ?>"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		
		<title><?php bloginfo( 'name' ); ?><?php wp_title( '—', true, 'left' ); ?></title>

		<link rel="profile" href="http://gmpg.org/xfn/11"/>
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>"/>
		<link rel="shortcut icon" href="<?php echo home_url( '/favicon.ico' ); ?>">


		<link rel="stylesheet" href="<?php echo home_url( '/assets/css/style.css' ); ?>" type="text/css">	

		<!-- <?php wp_enqueue_script("jquery"); ?>	 -->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

		<?php wp_head(); ?>
	
	</head>

	<body <?php body_class(); ?>>
		<div id="container">
			<div id="header">
				<div id="logo">
					<h2 class="site-name">
						<a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?>
						</a>
					</h2>
				</div>

				<button class="menu-toggle d-none">menu
					<span></span>
					<span></span>
					<span></span>
				</button>
				<?php wp_nav_menu( array( 'theme_location' => 'header-menu' ) ); ?>
			</div>
