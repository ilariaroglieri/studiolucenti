<?php
$text      = get_sub_field('text');
$alignment = get_sub_field('media_alignment');
$medium    = get_sub_field('all_row_media');
$medium_id = get_medium_id_from_acf($medium);

if ( ! $text && ! $medium_id ) return;

// senza media il testo prende tutta la riga, invece di lasciare mezza colonna vuota
$text_class = $medium_id ? 'd-half' : 'd-whole';
?>
<div class="d-flex flex-row <?= esc_attr($alignment); ?> m-column">
  <div class="text-element <?= esc_attr($text_class); ?> m-whole spacing-m-b-3">
    <div class="wysiwyg s-regular">
      <?= $text; ?>
    </div>
  </div>
  <?php if ( $medium_id ): ?>
    <div class="element d-half m-whole spacing-m-b-3">
      <?php render_media($medium_id, 6, false, true); ?>
    </div>
  <?php endif; ?>
</div>
