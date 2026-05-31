<?php
$text      = get_sub_field('text');
$alignment = get_sub_field('media_alignment');
$medium    = get_sub_field('all_row_media');
$medium_id = get_medium_id_from_acf($medium);
?>
<div class="d-flex flex-row <?= $alignment; ?> m-column">
  <div class="text-element d-half m-whole spacing-m-b-3">
    <div class="wysiwyg s-regular">
      <?= $text; ?>
    </div>
  </div>
  <div class="element d-half m-whole spacing-m-b-3">
    <?php render_media($medium_id, 6, false, true); ?>
  </div>
</div>
