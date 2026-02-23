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
    $heroRatio = $is_hero ? 'hero-container' : '';
    ?>

    <div class="media-container <?= $heroRatio; ?>">
      <?php if (str_starts_with($mime, 'video/')): ?>
        <video class="el bnd" muted loop autoplay playsinline>
          <source src="<?= esc_url(wp_get_attachment_url($medium_id)); ?>">
        </video>
      <?php else: 
        echo wp_get_attachment_image($medium_id, $size, false, ['class' => 'project_image', 'sizes' => $sizes, 'loading' => $loading]);
      endif; ?>
    </div>
<?php }