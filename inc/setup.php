<?php
	//kill gutenberg
	add_filter( 'use_block_editor_for_post', '__return_false' );

	//remove admin bar
	show_admin_bar(false);

	function register_my_menu() {
		register_nav_menu('header-menu',__( 'Header Menu' ));
	}
	add_action( 'init', 'register_my_menu' );


	// enqueue compiled bundle (JS + npm CSS)
	function theme_bundle_scripts() {
		$asset_file = get_template_directory() . '/build/index.asset.php';
		$asset = file_exists( $asset_file )
			? require( $asset_file )
			: [ 'version' => '1.0.0', 'dependencies' => [] ];

		wp_enqueue_script(
			'theme-bundle',
			get_template_directory_uri() . '/build/index.js',
			array_merge( [ 'jquery' ], $asset['dependencies'] ),
			$asset['version'],
			true
		);

		wp_enqueue_style(
			'theme-bundle-style',
			get_template_directory_uri() . '/build/index.css',
			[],
			$asset['version']
		);
	}
	add_action( 'wp_enqueue_scripts', 'theme_bundle_scripts' );

	

	function move_jquery_into_footer( $wp_scripts ) {

		if( is_admin() ) {
			return;
		}

		$wp_scripts->add_data( 'jquery', 'group', 1 );
		$wp_scripts->add_data( 'jquery-core', 'group', 1 );
		$wp_scripts->add_data( 'jquery-migrate', 'group', 1 );
	}
	add_action( 'wp_default_scripts', 'move_jquery_into_footer' );
	

	// enqueue theme CSS (depends on bundle CSS so it loads after and can override)
	//
	// La versione è `filemtime`, non un numero a mano e non l'hash di un commit.
	// Senza il quarto argomento WordPress ci scrive la **propria** versione —
	// `?ver=7.1` — che cambia solo quando si aggiorna il core: il foglio di stile
	// del tema restava nella cache dei visitatori da un rilascio all'altro.
	//
	// `filemtime` e non l'hash del commit per tre motivi: cambia solo quando
	// cambia *questo* file, mentre un hash di commit invalida ogni asset a ogni
	// rilascio e butta via cache ancora buona; funziona anche in locale, dove le
	// modifiche non sono ancora committate; e non richiede `.git` sul server, che
	// un deploy per rsync o FTP non porta con sé.
	//
	// Il bundle non ne ha bisogno: `build/index.asset.php` gli dà già l'hash del
	// contenuto, che è la stessa cosa fatta meglio da webpack.
	function register_theme_styles() {
		$style_path = get_template_directory() . '/assets/css/style.css';
		$style_ver  = file_exists( $style_path ) ? filemtime( $style_path ) : null;

		wp_register_style( 'style', get_template_directory_uri() . '/assets/css/style.css', [ 'theme-bundle-style' ], $style_ver );
		wp_enqueue_style( 'style' );
	}
	add_action( 'wp_enqueue_scripts', 'register_theme_styles' );

	function theme_enqueue_dashicons() {
		if ( is_user_logged_in() ) {
			wp_enqueue_style( 'dashicons' );
		}
	}
	add_action( 'wp_enqueue_scripts', 'theme_enqueue_dashicons' );

	add_action( 'init', function() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	} );


	// La options page del footer esisteva solo come record creato dall'interfaccia
	// ACF: su un ambiente nuovo il gruppo "Footer CF" non aveva dove agganciarsi e
	// the_field(..., 'option') non stampava nulla, in silenzio.
	add_action( 'acf/init', function () {
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			return;
		}

		// se è già registrata (record acf-ui-options-page) non duplicare la voce di menu
		if ( function_exists( 'acf_get_options_page' ) && acf_get_options_page( 'footer' ) ) {
			return;
		}

		acf_add_options_page( [
			'page_title' => 'Footer',
			'menu_title' => 'Footer',
			'menu_slug'  => 'footer',
			'capability' => 'edit_posts',
			'position'   => 25,
			'icon_url'   => 'dashicons-editor-insertmore',
			'redirect'   => false,
		] );
	}, 20 );
