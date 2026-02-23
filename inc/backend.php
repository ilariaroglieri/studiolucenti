<?php 

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
