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


  function get_circular_adjacent_post($post_id = null, $taxonomy = null, $terms = array(), $direction = 'next') {

    if (!$post_id) {
      $post_id = get_the_ID();
    }

    $post      = get_post($post_id);
    if (!$post) return null;

    $post_type = $post->post_type;
    $post_date = $post->post_date;

    $operator  = ($direction === 'prev') ? '<' : '>';
    $order     = ($direction === 'prev') ? 'DESC' : 'ASC';

    $args = array(
      'post_type'      => $post_type,
      'posts_per_page' => 1,
      'orderby'        => 'date',
      'order'          => $order,
      'post_status'    => 'publish',
      'date_query'     => array(
        array(
          'column'   => 'post_date',
          'compare'  => $operator,
          'inclusive'=> false,
          'before'   => ($direction === 'prev') ? $post_date : null,
          'after'    => ($direction === 'next') ? $post_date : null,
        )
      )
    );

    // filtro tassonomia opzionale
    if ($taxonomy && !empty($terms)) {
      $args['tax_query'] = array(
        array(
          'taxonomy' => $taxonomy,
          'field'    => 'slug',
          'terms'    => (array) $terms
        )
      );
    }

    $query = new WP_Query($args);

    // Se troviamo un risultato → return diretto
    if ($query->have_posts()) {
      return $query->posts[0];
    }

    // fallback circolare
    $args['date_query'] = null;
    $args['order']      = ($direction === 'prev') ? 'DESC' : 'ASC';

    $fallback_query = new WP_Query($args);

    if ($fallback_query->have_posts()) {
      return $fallback_query->posts[0];
    }

    return null;
  }

  function offusca_email_shortcode( $atts, $content = null ) {
    if ( ! is_email( $content ) ) {
        return $content;
    }
    return '<a href="mailto:' . antispambot( $content ) . '">' . antispambot( $content ) . '</a>';
}
add_shortcode( 'email', 'offusca_email_shortcode' );

?>

