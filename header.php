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
		<meta name="description" content="<?= esc_attr( studiolucenti_meta_description() ); ?>">
		
		<title><?php bloginfo( 'name' ); ?><?php wp_title( '—', true, 'left' ); ?></title>

		<?php // Gli hero e il reel stanno su Vimeo, e il primo segmento HLS non
		      // parte finché DNS, TCP e TLS verso due host nuovi non sono
		      // conclusi. Aprire le connessioni mentre il documento è ancora in
		      // parsing vale ~300ms misurati da Lighthouse. `skyfire` è il CDN
		      // che serve i segmenti, `player` il manifest.
		      //
		      // Solo dove un video Vimeo c'è davvero: un preconnect inutile è
		      // una connessione aperta e buttata. ?>
		<?php if ( is_front_page() || is_singular( 'post' ) ) : ?>
			<link rel="preconnect" href="https://player.vimeo.com" crossorigin>
			<link rel="preconnect" href="https://skyfire.vimeocdn.com" crossorigin>
		<?php endif; ?>

		<?php // i font sono il cammino critico: senza preload document.fonts.ready
		      // arrivava a 2,2s e teneva la pagina bianca fino ad allora ?>
		<link rel="preload" as="font" type="font/woff2" crossorigin href="<?= esc_url( get_template_directory_uri() . '/assets/fonts/PPMori-Regular.woff2' ); ?>">
		<link rel="preload" as="font" type="font/woff2" crossorigin href="<?= esc_url( get_template_directory_uri() . '/assets/fonts/PPMori-Semibold.woff2' ); ?>">
		<link rel="preload" as="font" type="font/woff2" crossorigin href="<?= esc_url( get_template_directory_uri() . '/assets/fonts/DMMono-Regular.woff2' ); ?>">

		<?php // gate del primo paint: la classe la toglie il bundle, il watchdog copre
		      // il caso in cui il bundle non arrivi. Senza JS non viene mai messa. ?>
		<script>
			(function () {
				var r = document.documentElement;
				r.classList.add('is-loading');
				setTimeout(function () { r.classList.remove('is-loading'); }, 2000);
			})();
		</script>

		<link rel="profile" href="http://gmpg.org/xfn/11"/>
		<?php wp_head(); ?>
	
	</head>

	<body <?php body_class(); ?>>
		<header role="banner" class="p-fixed">
			<div id="inner-header">
				<?php // In homepage il wordmark è `<h1>`, altrove `<h2>`: era l'unica
				      // pagina del sito senza nessun `<h1>`, e il wordmark è
				      // l'unico elemento a grande scala dell'header, quindi è lui.
				      // Sulle schede l'`<h1>` è già il titolo del progetto, e due
				      // sulla stessa pagina sarebbero uno di troppo.
				      //
				      // Il cambio di tag non tocca niente altro: la scala la dà
				      // `.s-big`, e lo specular sweep parte da `#site-name a`
				      // apposta per non accorgersi del tag. ?>
				<div id="logo">
					<?php $wordmark_tag = is_front_page() ? 'h1' : 'h2'; ?>
					<<?= $wordmark_tag; ?> id="site-name" class="s-big">
						<a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?>
						</a>
					</<?= $wordmark_tag; ?>>
				</div>

				<button aria-label="Main-Menu" class="menu-toggle d-none">
					<span></span>
					<span></span>
					<span></span>
				</button>
			</div>
			<?php wp_nav_menu( array( 'theme_location' => 'header-menu' ) ); ?>
		</header>
