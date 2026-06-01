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
	function register_theme_styles() {
		wp_register_style( 'style', get_template_directory_uri() . '/assets/css/style.css', [ 'theme-bundle-style' ] );
		wp_enqueue_style( 'style' );
	}
	add_action( 'wp_enqueue_scripts', 'register_theme_styles' );

	function theme_enqueue_dashicons() {
		if ( is_user_logged_in() ) {
			wp_enqueue_style( 'dashicons' );
		}
	}
	add_action( 'wp_enqueue_scripts', 'theme_enqueue_dashicons' );
?>
