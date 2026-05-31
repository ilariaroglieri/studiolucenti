<?php
$text          = get_sub_field('text');
$textAlignment = get_sub_field('text_alignment');
?>
<div class="d-flex flex-row <?= $textAlignment; ?>">
  <div class="text-element-lines d-two-thirds t-whole spacing-m-b-3">
    <div class="wysiwyg s-regular">
      <?= $text; ?>
    </div>
  </div>
</div>
