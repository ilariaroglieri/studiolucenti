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
  add_filter( 'manage_post_posts_columns' , 'add_acf_columns' );
  add_action('manage_post_posts_custom_column', 'size_info_column', 10, 2);
}
add_action( 'admin_init' , 'my_column_init' );


add_filter('acf/prepare_field/name=original_width', function( $field ) {
  $field['readonly'] = 1;
  return $field;
});

add_filter('acf/load_value/name=original_width', function ($value, $post_id, $field) { 

  if (!$post_id) return $value; 

  // extract repeater row index 
  if (!preg_match('/featured_projects_(\d+)_original_width/', $field['name'], $matches)) { 
    return $value; 
  } 

  $row_index = $matches[1]; 

  // get the featured project selected in this row 
  $featured_project_id = get_post_meta( 
    $post_id, 
    "featured_projects_{$row_index}_featured_project", 
    true 
  ); 

  if (!$featured_project_id) return $value; 

  // retrieve the value from the related post 
  $originalSize = get_field_object('featured_medium_size', $featured_project_id);
  $originalValue = get_field('featured_medium_size', $featured_project_id);
  $originalLabel = $originalSize['choices'][ $originalValue ];

  return $originalLabel; 

}, 10, 3);

add_action('acf/input/admin_head', function() {
  echo '<style>
    .acf-gallery .acf-gallery-uploader {
      max-height: 200px; /* Altezza massima per tutte le gallery */
      overflow-y: auto;  /* Scroll se ci sono più elementi */
    }
  </style>';
});
