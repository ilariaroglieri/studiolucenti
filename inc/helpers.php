<?php
  function get_post_terms( $post_id = null, $taxonomy = 'post_tag', $separator = ' ' ) {
    $post_id = $post_id ?: get_the_ID();

    $terms = get_the_terms( $post_id, $taxonomy );

    if ( empty( $terms ) || is_wp_error( $terms ) ) {
      return '';
    }

    $names = wp_list_pluck( $terms, 'name' );
    return implode( $separator, $names );
  }
?>