<?php
$video_url        = get_sub_field('video_url');
$video_poster     = get_sub_field('video_poster');
$size             = get_sub_field('one_media_size') ?: 'd-whole';
$alignment        = get_sub_field('media_alignment') ?: 'v-center';
$background_video = get_sub_field('background_video');
$aspect_ratio     = get_sub_field('aspect_ratio') ?: '16/9';

if ( ! $video_url ) return;

// stesso helper dell'hero e delle thumbnail: gli attributi dei <video> del
// sito stanno in un posto solo, in inc/media.php
$video_attrs = render_video_attrs([
  'autoplay' => (bool) $background_video,
  'controls' => ! $background_video,
  'hero'     => false,
  'poster'   => $video_poster,
  'class'    => $background_video ? 'bg-video' : 'hls-video',
]);
?>
<div class="d-flex flex-row<?= $size !== 'd-whole' ? ' ' . esc_attr($alignment) : ''; ?>">
  <div class="video-module-container <?= esc_attr($size); ?> m-whole" style="--video-aspect-ratio: <?= esc_attr($aspect_ratio); ?>">
    <video <?= $video_attrs; ?>>
      <source src="<?= esc_url($video_url); ?>">
    </video>
  </div>
</div>
