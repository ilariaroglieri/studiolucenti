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
	function jquery_scripts() {
		wp_enqueue_script( 'simplelightbox', get_stylesheet_directory_uri() . '/assets/js/simplelightbox/simple-lightbox.js', array(), '1.0.0', true );
		wp_enqueue_style( 'simplelightbox-style', get_stylesheet_directory_uri() . '/assets/js/simplelightbox/simplelightbox.css' );
		wp_enqueue_script( 'custom', get_stylesheet_directory_uri() . '/assets/js/custom.js', array(), '1.0.0', true );
	}

	add_action( 'wp_enqueue_scripts', 'jquery_scripts' );

	//enqueue css
	function register_theme_styles() {
	  wp_register_style( 'style', get_template_directory_uri() . '/assets/css/style.css' );
	  wp_enqueue_style( 'style' );
	}
	add_action( 'wp_enqueue_scripts', 'register_theme_styles' );
?>