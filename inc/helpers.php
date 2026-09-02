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

  /**
   * Email in markup che l'indirizzo per intero non ce l'ha.
   *
   * `antispambot()` codificava metà dei caratteri in entità HTML, e non
   * sopravvive: l'export statico riparsa l'HTML e le entità tornano caratteri,
   * quindi in produzione l'indirizzo finiva in chiaro — `mailto:` compreso.
   * Misurato sulla home esportata: zero entità numeriche in tutta la pagina, e
   * i caratteri che WordPress emette come `&#8217;` arrivano letterali.
   *
   * Qui l'indirizzo è spezzato in due elementi e la chiocciola nel markup non
   * c'è affatto: non resta nessuna sottostringa che somigli a un'email. La
   * rimette il CSS (`.js-email .email-domain::before`) per chi non ha JS, e il
   * JS sostituisce il tutto con il testo vero — che si può copiare, al
   * contrario di un `content` CSS — e aggiunge l'`href`.
   *
   * Senza JS il link non è cliccabile, ma l'indirizzo si legge: è il
   * compromesso, ed è dalla parte giusta. Un `href` in chiaro nel markup
   * vanificherebbe tutto il resto.
   */
  function offusca_email( $email, $class = '' ) {
    $email = sanitize_email( (string) $email );

    if ( ! is_email( $email ) ) {
      return '';
    }

    list( $user, $domain ) = explode( '@', $email, 2 );

    return sprintf(
      '<a class="js-email%s"><span class="email-user">%s</span><span class="email-domain">%s</span></a>',
      $class ? ' ' . esc_attr( $class ) : '',
      esc_html( $user ),
      esc_html( $domain )
    );
  }

  // `[email class="..."]indirizzo@dominio[/email]` nei WYSIWYG. Se il contenuto
  // non è un'email torna com'era: meglio un indirizzo sbagliato visibile che un
  // buco silenzioso.
  function offusca_email_shortcode( $atts, $content = null ) {
    $atts   = shortcode_atts( [ 'class' => '' ], $atts, 'email' );
    $markup = offusca_email( $content, $atts['class'] );

    return $markup ?: $content;
  }
  add_shortcode( 'email', 'offusca_email_shortcode' );
