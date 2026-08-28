<?php 
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

  /**
   * Attributi comuni a tutti i <video> del sito.
   *
   * Erano ripetuti a mano in tre punti — qui, nell'hero di single.php e nel
   * modulo video-row.php — e divergevano: `muted` mancava dove serviva,
   * `preload` era sempre quello di default. Vedi [[Video]].
   *
   * @param array $args autoplay|controls|hero|poster|width|height|class
   */
  function render_video_attrs( array $args = [] ) {
    $a = $args + [
      'autoplay' => true,   // loop decorativo: parte da solo
      'controls' => false,  // controlli custom, mai quelli nativi
      'hero'     => false,  // l'hero è l'LCP: precarica, e non va in lazy
      'poster'   => '',
      'width'    => 0,
      'height'   => 0,
      'class'    => '',
    ];

    $attrs = [];

    if ( $a['class'] ) {
      $attrs[] = sprintf( 'class="%s"', esc_attr( $a['class'] ) );
    }

    // playsinline sempre: senza, iOS apre il video a pieno schermo da solo
    $attrs[] = 'playsinline';

    if ( $a['autoplay'] ) {
      // `muted` non è una preferenza: senza, i browser bloccano l'autoplay
      // a prescindere, e il video resta un rettangolo fermo
      $attrs[] = 'autoplay';
      $attrs[] = 'muted';
      $attrs[] = 'loop';
    }

    if ( $a['controls'] ) {
      $attrs[] = 'controls';
    }

    $attrs[] = sprintf( 'preload="%s"', $a['hero'] ? 'auto' : 'none' );

    if ( $a['poster'] ) {
      $attrs[] = sprintf( 'poster="%s"', esc_url( $a['poster'] ) );
    }

    // width/height espliciti: è così che si tiene CLS < 0.05
    if ( $a['width'] && $a['height'] ) {
      $attrs[] = sprintf( 'width="%d" height="%d"', (int) $a['width'], (int) $a['height'] );
    }

    $attrs[] = 'disablepictureinpicture';

    return implode( ' ', $attrs );
  }

  /**
   * Poster di un allegato video. Nessun campo ACF nuovo: si usa l'immagine in
   * evidenza dell'allegato stesso (Media → il video → Immagine in evidenza),
   * che è dove WordPress la mette già.
   */
  function get_video_poster_url( $attachment_id, $size = 'grid-6' ) {
    $thumb_id = get_post_thumbnail_id( $attachment_id );
    if ( $thumb_id ) {
      return wp_get_attachment_image_url( $thumb_id, $size ) ?: '';
    }

    // fallback: la thumbnail che alcune installazioni generano all'upload
    $meta = wp_get_attachment_metadata( $attachment_id );
    return ! empty( $meta['image']['src'] ) ? $meta['image']['src'] : '';
  }

  /**
   * Sorgenti di un allegato video, WebM per primo.
   *
   * Convenzione: il WebM sta accanto all'MP4, stesso nome, estensione diversa —
   * è così che lo produce l'encoding dei loop. Se non c'è, resta solo l'MP4 e
   * non cambia niente. Il file_exists() costa uno stat per video, e il risultato
   * è nella cache del filesystem: su otto thumbnail non è misurabile.
   */
  function get_video_sources( $attachment_id ) {
    $url  = wp_get_attachment_url( $attachment_id );
    $mime = get_post_mime_type( $attachment_id );

    if ( ! $url ) {
      return [];
    }

    $sources = [];
    $path    = get_attached_file( $attachment_id );

    if ( $path && ! str_ends_with( strtolower( $path ), '.webm' ) ) {
      $webm_path = preg_replace( '/\.[^.\/]+$/', '.webm', $path );
      if ( $webm_path && file_exists( $webm_path ) ) {
        $sources[] = [
          'url'  => preg_replace( '/\.[^.\/]+$/', '.webm', $url ),
          'type' => 'video/webm',
        ];
      }
    }

    $sources[] = [ 'url' => $url, 'type' => $mime ?: 'video/mp4' ];

    return $sources;
  }

  // img attachment defaults
  function render_media($medium_id, $cols, $is_hero = false, $isLightbox = false) {
    if (!$medium_id) {
      return '';
    }

    // wp_get_attachment_metadata() è vuoto per parecchi allegati video: non è
    // un motivo per non stampare niente, serve solo per width/height
    $meta = wp_get_attachment_metadata($medium_id);
    $mime = get_post_mime_type($medium_id);

    $size_map = [
      12 => 'full-width',
      10 => 'full-width',
      9  => 'full-width',
      8  => 'grid-6',
      7  => 'grid-6',   // usa stessa size, cambia solo sizes
      6  => 'grid-6',
      5  => 'grid-6',   // idem
      4  => 'grid-4',
      3  => 'grid-4',   // idem
    ];

    $size = $size_map[$cols] ?? 'grid-6';

    //Calcolo percentuale viewport
    $percentage = ($cols / 12) * 100;

    $sizes = "(max-width: 768px) 100vw, {$percentage}vw";

    $loading = $is_hero ? 'eager' : 'lazy';
    $heroRatio = $is_hero ? 'hero-container' : '';
    ?>

    <?php // data-vt-media: l'aggancio della transizione FLIP. In griglia è la
          // thumbnail che parte, sulla scheda è l'hero che arriva: lo stesso
          // attributo su entrambi i lati, il nome lo assegna scroll.js al click ?>
    <div class="media-container <?= esc_attr($heroRatio); ?>" data-vt-media>
      <?php if (str_starts_with($mime, 'video/')):
        // Fuori dall'hero la sorgente non viene assegnata dal markup: la mette
        // initLazyVideos() in custom.js quando l'elemento entra in viewport.
        // Con movimento ridotto non la mette mai e resta il poster.
        $is_lazy    = ! $is_hero;
        $src_attr   = $is_lazy ? 'data-src' : 'src';
        $sources    = get_video_sources($medium_id);
        $video_attrs = render_video_attrs([
          'hero'   => $is_hero,
          'poster' => get_video_poster_url($medium_id, $size),
          'width'  => $meta['width']  ?? 0,
          'height' => $meta['height'] ?? 0,
          'class'  => 'el bnd' . ($is_lazy ? ' js-lazy-video' : ''),
        ]);
        ?>
        <video <?= $video_attrs; ?>>
          <?php foreach ($sources as $source): ?>
            <source <?= $src_attr; ?>="<?= esc_url($source['url']); ?>" type="<?= esc_attr($source['type']); ?>">
          <?php endforeach; ?>
        </video>
      <?php else: ?>
        <?php if ($isLightbox): 
          $attachmentUrl = wp_get_attachment_image_url($medium_id, 'full-width');
          ?>
          <a class="single-lightbox-el" aria-label="<?= esc_attr(get_post_meta($medium_id, '_wp_attachment_image_alt', true)); ?>" href="<?= esc_url($attachmentUrl); ?>">
           <?= wp_get_attachment_image($medium_id, $size, false, ['class' => 'project_image', 'sizes' => $sizes, 'loading' => $loading]); ?>
          </a>
        <?php else:
          echo wp_get_attachment_image($medium_id, $size, false, ['class' => 'project_image', 'sizes' => $sizes, 'loading' => $loading]); ?>
        <?php endif; ?>
      <?php endif; ?>
    </div>
<?php }

  function displayGridProject($home_size) {
    $featured_medium = get_field('featured_medium');
    $featured_medium_size = get_field('featured_medium_size');

    // l'override della home vale solo se valorizzato: il select ha allow_null e
    // torna stringa vuota, che con !== null passava e svuotava la classe
    $curr_size = $home_size ?: ($featured_medium_size ?: 'd-half');
    $medium_id = get_medium_id_from_acf($featured_medium); 
    $width = match ($curr_size) {
      'd-whole' => 12,
      'd-10-twelfth' => 12,
      'd-two-thirds' => 12,
      'd-7-twelfth' => 7,
      'd-half' => 6,
      'd-5-twelfth' => 5,
      'd-one-third' => 4,
      default => 6,
    };
  ?>

  <project id="post-<?php the_ID(); ?>" class="<?= esc_attr($curr_size); ?> project m-whole p-relative spacing-b-3 spacing-t-3 spacing-m-b-2 spacing-m-t-2">
    <a class="p-absolute overall" aria-label="<?php the_title(); ?>" href="<?php the_permalink(); ?>"></a>
    <h2 class="project-title s-regular spacing-b-half"><?php the_title(); ?></h2>
    <?php render_media($medium_id, $width, false); ?>
  </project>
<?php }
