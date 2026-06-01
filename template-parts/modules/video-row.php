<?php
$video_url        = get_sub_field('video_url');
$video_poster     = get_sub_field('video_poster');
$size             = get_sub_field('one_media_size') ?: 'd-whole';
$background_video = get_sub_field('background_video');
$aspect_ratio     = get_sub_field('aspect_ratio') ?: '16/9';

if ( ! $video_url ) return;

if ( $background_video ) {
  $video_class = 'bg-video';
  $video_attrs = 'autoplay loop muted playsinline';
} else {
  $video_class = 'hls-video';
  $video_attrs = 'controls playsinline';
}
?>
<div class="d-flex flex-row<?= $size !== 'd-whole' ? ' v-center' : ''; ?>">
  <div class="video-module-container <?= $size; ?> m-whole" style="--video-aspect-ratio: <?= esc_attr($aspect_ratio); ?>">
    <video class="<?= $video_class; ?>" <?= $video_attrs; ?><?php if ($video_poster): ?> poster="<?= esc_url($video_poster); ?>"<?php endif; ?>>
      <source src="<?= esc_url($video_url); ?>">
    </video>
  </div>
</div>
