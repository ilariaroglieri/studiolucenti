<?php 

// add acf width column to projects admin page
function add_acf_columns($columns) {
  $columns['size'] = 'Display size';
  return $columns;
}

function size_info_column($column, $post_id) {
  if ( $column == 'size' ) {
    // Get the field object to fetch choices
    $size = get_field_object('featured_medium_size', $post_id);
    $value = get_field('featured_medium_size');;
    $label = $size['choices'][ $value ];

    if ($size) {
      echo $label;
    } else {
      echo 'Default (6 col)';  // Or whatever you'd like to display when the field is empty
    }
  }
}

function my_column_init() {
  echo get_current_screen();
  add_filter( 'manage_post_posts_columns' , 'add_acf_columns' );
  add_action('manage_post_posts_custom_column', 'size_info_column', 10, 2);
}
add_action( 'admin_init' , 'my_column_init' );


// NON FUNZIONA AL MOMENTO
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

    $related_post = $row['featured_project'];
    $rp_ID = $related_post->ID;

    $original_value = get_field('featured_medium_size', $rp_ID);
    var_dump($original_value);  // restitusice i giusti valori


    $row['original_width'] = $original_value;

  }

  return $field;
});

add_action('acf/input/admin_head', function() {
  echo '<style>
    .acf-gallery .acf-gallery-uploader {
      max-height: 200px; /* Altezza massima per tutte le gallery */
      overflow-y: auto;  /* Scroll se ci sono più elementi */
    }
  </style>';
});

?>
