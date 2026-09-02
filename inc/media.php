<?php
  add_theme_support('post-thumbnails');

  // Senza questo, l'allegato video non ha nessun pannello "Immagine in
  // evidenza" in admin: add_theme_support('post-thumbnails') attiva il tema,
  // ma il post type 'attachment' non ha 'thumbnail' tra i suoi supports di
  // default. get_video_poster() legge get_post_thumbnail_id() sull'allegato:
  // senza questa riga, quel campo non è compilabile da nessuna parte.
  add_post_type_support('attachment', 'thumbnail');

  add_image_size('full-width', 2560, 0, false);
  add_image_size('full-width-mobile', 768, 0, false);
  add_image_size('grid-6', 1536, 0, false);
  add_image_size('grid-4', 900, 0, false);

  /**
   * I campi media del tema accettano anche video.
   *
   * ACF 6.8.7 ha reso il campo `gallery` image-only: acf_is_image_field()
   * considera image-only ogni campo di tipo `image` o `gallery`, e l'allegato
   * video viene marcato "Restricted — File must be a valid image" nel modale.
   * Questi tre campi però nascono per accettare entrambi — lo dicono le loro
   * stesse etichette — e render_media() fa il branch sul mime type.
   *
   * `acf/is_image_field/name=...` è il punto di estensione che ACF ha aggiunto
   * nella stessa versione della restrizione, apposta per questi casi.
   *
   * `featured_medium` non è in lista: è stato convertito in campo `file`, che
   * la restrizione non tocca. Restano questi due perché cambiarli costa di più:
   * `hero_medium` è max=1 e si potrebbe convertire allo stesso modo, mentre
   * `all_row_media` è multi-valore (max=3) e richiederebbe un repeater e 492
   * righe da ristrutturare. Vedi [[Decisioni]] → D8.
   */
  const STUDIOLUCENTI_MEDIA_FIELDS = [ 'hero_medium', 'all_row_media' ];

  foreach ( STUDIOLUCENTI_MEDIA_FIELDS as $field_name ) {
    add_filter( "acf/is_image_field/name={$field_name}", '__return_false' );
  }

  /**
   * La restrizione ha un secondo punto d'ingresso, non filtrabile.
   *
   * ACF_Field_Gallery::validate_value() chiama wp_attachment_is_image() a mano,
   * senza passare da acf_is_image_field(): il filtro sopra copre il modale e
   * l'upload, ma non il salvataggio. Qui si annulla *solo* quell'errore,
   * confrontandolo con la stessa stringa tradotta che ACF produce, così le
   * altre validazioni del campo (il minimo di selezioni) restano attive.
   *
   * L'ordine è garantito: acf_validate_value() applica i filtri `type=` prima
   * dei `name=` (includes/validation.php:355-357).
   */
  function studiolucenti_allow_video_in_media_field( $valid ) {
    if ( is_string( $valid ) && $valid === __( 'File must be a valid image.', 'acf' ) ) {
      return true;
    }
    return $valid;
  }

  foreach ( STUDIOLUCENTI_MEDIA_FIELDS as $field_name ) {
    add_filter( "acf/validate_value/name={$field_name}", 'studiolucenti_allow_video_in_media_field', 20 );
  }


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
      'style'    => '',
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

    if ( $a['style'] ) {
      $attrs[] = sprintf( 'style="%s"', esc_attr( $a['style'] ) );
    }

    $attrs[] = 'disablepictureinpicture';

    return implode( ' ', $attrs );
  }

  /**
   * Poster di un allegato video, con le sue dimensioni.
   *
   * Nessun campo ACF nuovo: si usa l'immagine in evidenza dell'allegato stesso
   * (Media → il video → Immagine in evidenza), che è dove WordPress la mette già.
   *
   * Le dimensioni servono quanto l'URL. `wp_get_attachment_metadata()` è vuoto
   * per parecchi allegati video, e un <video> senza `src` — quelli in lazy non
   * ce l'hanno — non ha nessun altro modo di conoscere le proprie proporzioni:
   * il box collassa all'altezza di default finche il poster non arriva.
   * Il poster e il primo frame, quindi le sue proporzioni sono quelle giuste.
   *
   * @return array{url: string, width: int, height: int}
   */
  function get_video_poster( $attachment_id, $size = 'grid-6' ) {
    $thumb_id = get_post_thumbnail_id( $attachment_id );
    if ( $thumb_id ) {
      $src = wp_get_attachment_image_src( $thumb_id, $size );
      if ( $src && ! empty( $src[0] ) ) {
        return [ 'url' => $src[0], 'width' => (int) $src[1], 'height' => (int) $src[2] ];
      }
    }

    // fallback: la thumbnail che alcune installazioni generano all'upload
    $meta = wp_get_attachment_metadata( $attachment_id );
    if ( ! empty( $meta['image']['src'] ) ) {
      return [
        'url'    => $meta['image']['src'],
        'width'  => (int) ( $meta['image']['width'] ?? 0 ),
        'height' => (int) ( $meta['image']['height'] ?? 0 ),
      ];
    }

    return [ 'url' => '', 'width' => 0, 'height' => 0 ];
  }


  /**
   * URL del poster del reel di homepage, vuoto se il reel non viene stampato.
   *
   * Serve in due posti che devono restare d'accordo: l'attributo `poster` del
   * <video> in front-page.php e il <link rel="preload"> in header.php. Se le
   * due URL divergono il preload non viene mai riscattato e l'immagine si
   * scarica due volte — quindi la sorgente dev'essere una sola, e la
   * dimensione ('full-width') si cambia qui per entrambi.
   *
   * Le condizioni sono le stesse del reel: interruttore acceso, URL HLS
   * presente, poster caricato. In header.php non c'è nessun loop aperto,
   * quindi l'ID della pagina va passato a mano.
   */
  function studiolucenti_reel_poster_url() {
    $page_id = (int) get_queried_object_id();

    if ( ! $page_id || ! function_exists( 'get_field' ) ) {
      return '';
    }

    if ( ! get_field( 'show_video_reel', $page_id ) || ! get_field( 'video_reel', $page_id ) ) {
      return '';
    }

    // Il campo torna un ID, ma passa da get_medium_id_from_acf() come tutti
    // gli altri campi media del tema: se il return_format cambiasse in admin
    // arriverebbe un array, e wp_get_attachment_image_url() non se ne
    // accorgerebbe — restituirebbe false e il poster sparirebbe in silenzio.
    $poster_id = get_medium_id_from_acf( get_field( 'video_reel_poster', $page_id ) );

    return $poster_id ? (string) wp_get_attachment_image_url( $poster_id, 'full-width' ) : '';
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
  /**
   * @param bool $eager_poster Stampa il poster nel markup invece di lasciarlo
   *                           all'IntersectionObserver. Vale per le prime
   *                           thumbnail della griglia: sono l'elemento LCP e
   *                           non possono aspettare che parta il JS.
   */
  function render_media($medium_id, $cols, $is_hero = false, $isLightbox = false, $eager_poster = false) {
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
        $is_lazy  = ! $is_hero;
        $src_attr = $is_lazy ? 'data-src' : 'src';

        // Anche il **poster** va in lazy, e non è un dettaglio: `poster` non ha
        // un equivalente di `loading="lazy"`, quindi il browser li scarica
        // tutti insieme al parsing. Su /work/, ventitré progetti, sono ~700 KB
        // presi al caricamento per mostrarne due. Misurato: tutte e 23 le
        // richieste partono allo stesso millisecondo.
        //
        // Le prime thumbnail restano `poster` nel markup: sono l'elemento più
        // grande sopra la piega, cioè il candidato LCP, e farle dipendere dal
        // JS sposterebbe l'LCP dopo il bundle. Il risparmio sta nelle altre
        // venti, non in queste.
        $lazy_poster = $is_lazy && ! $eager_poster;
        $poster_attr = $lazy_poster ? 'data-poster' : 'poster';
        $sources  = get_video_sources($medium_id);
        $poster   = get_video_poster($medium_id, $size);

        // Il box va riservato *sempre*, o il documento nasce molto piu corto di
        // quanto sara, e tutto quello che sta sotto slitta quando i poster
        // arrivano. Tre fonti in ordine di attendibilita: i metadati del video,
        // le dimensioni del poster (che e il primo frame, quindi ha le stesse
        // proporzioni), e in ultima istanza 16/9 - meglio una proporzione
        // plausibile che nessuna.
        $video_w = (int) ($meta['width']  ?? 0);
        $video_h = (int) ($meta['height'] ?? 0);
        if (!$video_w || !$video_h) {
          $video_w = $poster['width'];
          $video_h = $poster['height'];
        }

        $video_attrs = render_video_attrs([
          // Loop muto in autoplay sempre, in griglia come in hero. In hero
          // (fallback quando il progetto non ha un embed Vimeo) arrivano anche
          // i controlli, che pero' restano nascosti finche' non si clicca il
          // cerchio al centro — stesso comportamento del branch embed in
          // single.php, agganciato da .hero-video.
          'autoplay' => true,
          'controls' => $is_hero,
          'hero'     => $is_hero,
          // il box resta riservato lo stesso: width/height e il fallback
          // `aspect-ratio` non dipendono da dove sta l'URL del poster
          'poster'   => $lazy_poster ? '' : $poster['url'],
          'width'    => $video_w,
          'height'   => $video_h,
          'style'    => ($video_w && $video_h) ? '' : 'aspect-ratio: 16 / 9',
          'class'    => 'el bnd' . ($is_hero ? ' hero-video' : '') . ($is_lazy ? ' js-lazy-video' : ''),
        ]);
        ?>
        <video <?= $video_attrs; ?><?= $lazy_poster && $poster['url'] ? ' data-poster="' . esc_url($poster['url']) . '"' : ''; ?>>
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

  // Quante thumbnail tengono il poster nel markup prima che subentri il lazy.
  // Due: una riga di `d-half` a piena larghezza, che su 1440×900 occupa da sola
  // tutta la piega. Sotto ci sono già venti richieste risparmiate.
  const STUDIOLUCENTI_EAGER_POSTERS = 2;

  function displayGridProject($home_size) {
    // per richiesta, non per pagina: `static` si azzera a ogni caricamento
    static $rendered = 0;
    $eager_poster = $rendered++ < STUDIOLUCENTI_EAGER_POSTERS;

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
    <?php render_media($medium_id, $width, false, false, $eager_poster); ?>
  </project>
<?php }
