<?php
$images = get_sub_field('gallery');
$size   = get_sub_field('media_size') ?: 'd-whole';

if ( ! is_array($images) || ! $images ) return;

// stessa scala di render_media(): niente file originale servito a piena risoluzione
$cols = match ($size) {
  'd-whole'      => 12,
  'd-10-twelfth' => 10,
  'd-two-thirds' => 8,
  'd-7-twelfth'  => 7,
  'd-half'       => 6,
  'd-5-twelfth'  => 5,
  'd-one-third'  => 4,
  default        => 12,
};
$img_size   = $cols >= 9 ? 'full-width' : ($cols >= 5 ? 'grid-6' : 'grid-4');
$img_sizes  = '(max-width: 768px) 100vw, ' . ( ($cols / 12) * 100 ) . 'vw';
?>
<div class="d-flex flex-row<?= $size !== 'd-whole' ? ' v-center' : ''; ?>">
  <div class="slider-container <?= esc_attr($size); ?> m-whole">
    <div class="slider-track">
      <button class="slider-nav slider-nav-prev" aria-label="Previous slide">
        <svg width="27" height="46" viewBox="0 0 27 46" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M25.1965 1.08765L2.19653 22.9289L25.1965 44.0876" stroke="currentColor" stroke-width="2"/>
        </svg>
      </button>
      <div class="swiper slider-module">
        <div class="swiper-wrapper">
          <?php foreach ($images as $index => $image): ?>
            <div class="swiper-slide">
              <?= wp_get_attachment_image($image['ID'], $img_size, false, [
                'sizes'   => $img_sizes,
                // le slide fuori schermo sono trasformate, non scrollate: lazy le
                // caricherebbe solo allo swipe. Eager solo sulla prima.
                'loading' => $index === 0 ? 'eager' : 'lazy',
              ]); ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <button class="slider-nav slider-nav-next" aria-label="Next slide">
        <svg width="27" height="46" viewBox="0 0 27 46" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M1.03272 1.08765L24.0327 22.9289L1.03272 44.0876" stroke="currentColor" stroke-width="2"/>
        </svg>
      </button>
    </div>
    <div class="slider-fraction"></div>
  </div>
</div>
