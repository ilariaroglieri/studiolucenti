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
	wp_enqueue_script( 'custom', get_stylesheet_directory_uri() . '/assets/js/custom.js', array(), '1.0.0', true );
}

add_action( 'wp_enqueue_scripts', 'jquery_scripts' );

//enqueue css
function register_theme_styles() {
  wp_register_style( 'style', get_template_directory_uri() . '/assets/css/style.css' );
  wp_enqueue_style( 'style' );
}
add_action( 'wp_enqueue_scripts', 'register_theme_styles' );

add_theme_support( 'post-thumbnails' ); 

// remove_filter('the_content', 'wpautop');

//Page Slug Body Class
function add_slug_body_class( $classes ) {
	global $post;
	if ( isset( $post ) ) {
		$classes[] = $post->post_type . '-' . $post->post_name;
	}
	return $classes;
}
add_filter( 'body_class', 'add_slug_body_class' );

// img attachment defaults

function default_attachment_display_settings() {
    update_option( 'image_default_link_type', 'none' );
    update_option( 'image_default_size', 'full' );
}

add_action( 'after_setup_theme', 'default_attachment_display_settings' );

// options page
if( function_exists('acf_add_options_page') ) {
  acf_add_options_page(array(
    'page_title'  => 'Settings',
    'menu_title'  => 'Settings',
    'menu_slug'   => 'settings',
    'capability'  => 'edit_posts',
    'redirect'    => false
  ));
}

add_action('admin_head', 'my_custom_wysiwyg');
function my_custom_wysiwyg() {
  echo '<style>
    .acf-editor-wrap iframe {
      min-height: 100px !important;
      height: 100px;
    } 
    .acf-gallery {
    	height: 200px !important;
    }
    .wp-editor-area {
    	height: 70px !important;
    }
  </style>';
}


?>