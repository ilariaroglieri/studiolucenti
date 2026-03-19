<?php
	//kill gutenberg
	add_filter( 'use_block_editor_for_post', '__return_false' );

	//remove admin bar
	show_admin_bar(false);

	function register_my_menu() {
		register_nav_menu('header-menu',__( 'Header Menu' ));
	}
	add_action( 'init', 'register_my_menu' );


	// enqueue scripts
	function custom_video_player_scripts() {
    wp_enqueue_script('hls-js', 'https://cdn.jsdelivr.net/npm/hls.js@latest', [], null, true);
		wp_enqueue_style( 'plyr-style', 'https://cdn.plyr.io/3.7.8/plyr.css' );
    wp_enqueue_script('plyr', 'https://cdn.plyr.io/3.7.8/plyr.polyfilled.js', [], null, true);
	}
	add_action('wp_enqueue_scripts', 'custom_video_player_scripts');

	function lightbox_scripts() {
		wp_enqueue_script( 'simplelightbox', get_stylesheet_directory_uri() . '/assets/js/simplelightbox/simple-lightbox.js', array(), '1.0.0', true );
		wp_enqueue_style( 'simplelightbox-style', get_stylesheet_directory_uri() . '/assets/js/simplelightbox/simplelightbox.css' );
	}
	add_action( 'wp_enqueue_scripts', 'lightbox_scripts' );

	function lenis_scripts() {
		wp_enqueue_script( 'lenis', 'https://unpkg.com/lenis@1.3.19/dist/lenis.min.js', array(), '', true );
	}
	add_action( 'wp_enqueue_scripts', 'lenis_scripts' );

	function theme_gsap_script(){
    // The core GSAP library
    wp_enqueue_script( 'gsap-js', 'https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js', array(), false, true );
    // ScrollTrigger - with gsap.js passed as a dependency
    wp_enqueue_script( 'gsap-st', 'https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/ScrollTrigger.min.js', array('gsap-js'), false, true );
    // Your animation code file - with gsap.js passed as a dependency
    wp_enqueue_script( 'gsap-js2', get_template_directory_uri() . '/assets/js/scroll.js', array('gsap-js'), false, true );
	}

	add_action( 'wp_enqueue_scripts', 'theme_gsap_script' );

	function js_scripts() {
		wp_enqueue_script( 'helpers', get_stylesheet_directory_uri() . '/assets/js/helpers.js', array(), '1.0.0', true );
		wp_enqueue_script( 'custom', get_stylesheet_directory_uri() . '/assets/js/custom.js', array(), '1.0.0', true );
	}
	add_action( 'wp_enqueue_scripts', 'js_scripts' );

	//enqueue css
	function register_theme_styles() {
	  wp_register_style( 'style', get_template_directory_uri() . '/assets/css/style.css' );
	  wp_enqueue_style( 'style' );
	}
	add_action( 'wp_enqueue_scripts', 'register_theme_styles' );
?>
