<?php
$media      = get_sub_field('all_row_media');
$alignment  = get_sub_field('media_alignment');
$twoSizes   = get_sub_field('two_media_sizes');
$singleSize = get_sub_field('one_media_size');
$count      = count($media);
?>
<div class="d-flex flex-row <?= $alignment; ?> m-column">
  <?php foreach ($media as $index => $m):
    $class   = 'd-half';
    $imgSize = 6;

    if ($count === 1) {
      $class   = $singleSize ?: 'd-whole';
      $sizeMap = ['d-whole' => 12, 'd-10-twelfth' => 10, 'd-two-thirds' => 8, 'd-7-twelfth' => 7, 'd-half' => 6, 'd-5-twelfth' => 5, 'd-one-third' => 4];
      $imgSize = $sizeMap[$class] ?? 12;
    } elseif ($count === 2) {
      if ($twoSizes === 'd-half') {
        $class   = 'd-half';
        $imgSize = 6;
      } elseif ($twoSizes === 'd-one-third') {
        $class   = ($index === 0) ? 'd-5-twelfth' : 'd-7-twelfth';
        $imgSize = ($index === 0) ? 4 : 6;
      } elseif ($twoSizes === 'd-two-thirds') {
        $class   = ($index === 0) ? 'd-7-twelfth' : 'd-5-twelfth';
        $imgSize = ($index === 0) ? 6 : 4;
      }
    } elseif ($count === 3) {
      $class   = 'd-one-third';
      $imgSize = 4;
    }

    $medium_id = get_medium_id_from_acf($m);
  ?>
    <div class="element <?= $class; ?> m-whole spacing-m-b-3">
      <?php render_media($medium_id, $imgSize, false, true); ?>
    </div>
  <?php endforeach; ?>
</div>
