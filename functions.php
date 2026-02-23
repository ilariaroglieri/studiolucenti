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



add_theme_support('post-thumbnails');

add_image_size('full-width', 2560, 0, false);
add_image_size('full-width-mobile', 768, 0, false);
add_image_size('grid-6', 1536, 0, false);
add_image_size('grid-4', 900, 0, false);


// retrieve ID from ACF field (supports galleries or single images)
function get_medium_id_from_acf($field)  {
  // se è array (gallery o singolo image array)
  if (is_array($field)) {
    if (!empty($field['ID'])) {
      return (int) $field['ID'];
    } elseif (!empty($field[0]['ID'])) {
        // gallery
      return (int) $field[0]['ID'];
    }
  }

  // se è già un ID numerico
  if (is_numeric($field)) {
    return (int) $field;
  }

  return null;
}

// img attachment defaults
function render_media($medium_id, $cols, $is_hero = false, $context = 'default') {
  $meta = wp_get_attachment_metadata($medium_id);

  if (!$meta) {
    return '';
  }

  $width  = $meta['width'];
  $height = $meta['height'];

  $is_vertical = $height > $width;

  $mime = get_post_mime_type($medium_id, $cols);

  // in homepage le verticali si rimpicciliscono
  if ($context === 'homepage' && $is_vertical) {

    if ($cols == 6) {
      $cols = 5;
    }

    if ($cols == 4) {
      $cols = 3;
    }
  }

  $size_map = [
    12 => 'full-width',
    10 => 'full-width',
    9  => 'full-width',
    6  => 'grid-6',
    5  => 'grid-6',   // usa stessa size, cambia solo sizes
    4  => 'grid-4',
    3  => 'grid-4',   // idem
  ];

  $size = $size_map[$cols] ?? 'grid-6';


  //Calcolo percentuale viewport
  $percentage = ($cols / 12) * 100;

  $sizes = "(max-width: 768px) 100vw, {$percentage}vw";

  $loading = $is_hero ? 'eager' : 'lazy';

  if (str_starts_with($mime, 'video/')): ?>
    <video class="el bnd" muted loop autoplay playsinline>
      <source src="<?= esc_url(wp_get_attachment_url($medium_id)); ?>">
    </video>
  <?php else: 
    echo wp_get_attachment_image($medium_id, $size, false, ['class' => 'project_image', 'sizes' => $sizes, 'loading' => $loading]);
  endif;
}

/**
 * Rende il campo original_width readonly
 */
add_filter('acf/load_field/name=original_width', function($field) {
    $field['readonly'] = 1;
    return $field;
});

add_filter('acf/prepare_field/name=featured_projects', function($field) {

    $post_id = acf_get_form_data('post_id'); // ID della pagina corrente
    if (!$post_id) return $field;


    // Recupera tutte le righe del repeater
    $rows = get_field('featured_projects', $post_id);
    if (empty($rows) || !is_array($rows)) return $field;

    // print "<pre>";
    // print_r($rows);
    // print "</pre>";


    foreach ($rows as $index => $row) {

      if (empty($row['featured_project'])) continue;

      $related_post = $row['featured_project'][0];
      $rp_ID = $related_post->ID;

      $original_value = get_field('featured_medium_size', $rp_ID);
      var_dump($original_value);  // restitusice i giusti valori


      $row['original_width'] = $original_value;

    }

    return $field;
});

// rename default post type
function rename_default_post_label() {
  global $menu;
  global $submenu;
  $menu[5][0] = 'Projects';
  $submenu['edit.php'][5][0] = 'Projects';
  $submenu['edit.php'][10][0] = 'Add Project';
  $submenu['edit.php'][16][0] = 'Projects Tags';
}
function rename_default_post_object() {
  global $wp_post_types;
  $labels = &$wp_post_types['post']->labels;
  $labels->name = 'Projects';
  $labels->singular_name = 'Project';
  $labels->add_new = 'Add Project';
  $labels->add_new_item = 'Add Project';
  $labels->edit_item = 'Edit Project';
  $labels->new_item = 'Projects';
  $labels->view_item = 'View Projects';
  $labels->search_items = 'Search Projects';
  $labels->not_found = 'No Projects found';
  $labels->not_found_in_trash = 'No Projects found in Trash';
  $labels->all_items = 'All Projects';
  $labels->menu_name = 'Projects';
  $labels->name_admin_bar = 'Projects';
}
 
add_action( 'admin_menu', 'rename_default_post_label' );
add_action( 'init', 'rename_default_post_object' );

// --- TAX
// Register Custom Taxonomy ANNO
function project_year_taxonomy() {

  $labels = array(
    'name'                       => _x( 'Year', 'Taxonomy General Name', 'text_domain' ),
    'singular_name'              => _x( 'Year', 'Taxonomy Singular Name', 'text_domain' ),
    'menu_name'                  => __( 'Years', 'text_domain' ),
    'all_items'                  => __( 'All Items', 'text_domain' ),
    'parent_item'                => __( 'Parent Item', 'text_domain' ),
    'parent_item_colon'          => __( 'Parent Item:', 'text_domain' ),
    'new_item_name'              => __( 'New Item Name', 'text_domain' ),
    'add_new_item'               => __( 'Add New Item', 'text_domain' ),
    'edit_item'                  => __( 'Edit Item', 'text_domain' ),
    'update_item'                => __( 'Update Item', 'text_domain' ),
    'view_item'                  => __( 'View Item', 'text_domain' ),
    'separate_items_with_commas' => __( 'Separate items with commas', 'text_domain' ),
    'add_or_remove_items'        => __( 'Add or remove items', 'text_domain' ),
    'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
    'popular_items'              => __( 'Popular Items', 'text_domain' ),
    'search_items'               => __( 'Search Items', 'text_domain' ),
    'not_found'                  => __( 'Not Found', 'text_domain' ),
    'no_terms'                   => __( 'No items', 'text_domain' ),
    'items_list'                 => __( 'Items list', 'text_domain' ),
    'items_list_navigation'      => __( 'Items list navigation', 'text_domain' ),
  );
  $args = array(
    'labels'                     => $labels,
    'hierarchical'               => true,
    'public'                     => true,
    'show_ui'                    => true,
    'show_admin_column'          => true,
    'show_in_nav_menus'          => true,
    'show_tagcloud'              => true,
    'show_in_rest'               => true,
  );
  register_taxonomy( 'project_year', array( 'post' ), $args );
}
add_action( 'init', 'project_year_taxonomy', 0 );

// Register Custom Taxonomy ANNO
function project_client_taxonomy() {

  $labels = array(
    'name'                       => _x( 'Client', 'Taxonomy General Name', 'text_domain' ),
    'singular_name'              => _x( 'Client', 'Taxonomy Singular Name', 'text_domain' ),
    'menu_name'                  => __( 'Clients', 'text_domain' ),
    'all_items'                  => __( 'All Items', 'text_domain' ),
    'parent_item'                => __( 'Parent Item', 'text_domain' ),
    'parent_item_colon'          => __( 'Parent Item:', 'text_domain' ),
    'new_item_name'              => __( 'New Item Name', 'text_domain' ),
    'add_new_item'               => __( 'Add New Item', 'text_domain' ),
    'edit_item'                  => __( 'Edit Item', 'text_domain' ),
    'update_item'                => __( 'Update Item', 'text_domain' ),
    'view_item'                  => __( 'View Item', 'text_domain' ),
    'separate_items_with_commas' => __( 'Separate items with commas', 'text_domain' ),
    'add_or_remove_items'        => __( 'Add or remove items', 'text_domain' ),
    'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
    'popular_items'              => __( 'Popular Items', 'text_domain' ),
    'search_items'               => __( 'Search Items', 'text_domain' ),
    'not_found'                  => __( 'Not Found', 'text_domain' ),
    'no_terms'                   => __( 'No items', 'text_domain' ),
    'items_list'                 => __( 'Items list', 'text_domain' ),
    'items_list_navigation'      => __( 'Items list navigation', 'text_domain' ),
  );
  $args = array(
    'labels'                     => $labels,
    'hierarchical'               => true,
    'public'                     => true,
    'show_ui'                    => true,
    'show_admin_column'          => true,
    'show_in_nav_menus'          => true,
    'show_tagcloud'              => true,
    'show_in_rest'               => true,
  );
  register_taxonomy( 'project_client', array( 'post' ), $args );
}
add_action( 'init', 'project_client_taxonomy', 0 );

// add_action('admin_head', 'my_custom_wysiwyg');
// function my_custom_wysiwyg() {
//   echo '<style>
//     .acf-editor-wrap iframe {
//       min-height: 100px !important;
//       height: 100px;
//     } 
//     .acf-gallery {
//     	height: 200px !important;
//     }
//     .wp-editor-area {
//     	height: 70px !important;
//     }
//   </style>';
// }


?>