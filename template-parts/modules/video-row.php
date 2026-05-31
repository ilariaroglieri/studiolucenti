<?php
$video_url    = get_sub_field('video_url');
$video_poster = get_sub_field('video_poster');
$size         = get_sub_field('one_media_size') ?: 'd-whole';

if ( ! $video_url ) return;
?>
<div class="d-flex flex-row<?= $size !== 'd-whole' ? ' v-center' : ''; ?>">
  <div class="video-module-container <?= $size; ?> m-whole">
    <video class="hls-video" controls playsinline<?php if ($video_poster): ?> poster="<?= esc_url($video_poster); ?>"<?php endif; ?>>
      <source src="<?= esc_url($video_url); ?>">
    </video>
  </div>
</div>
