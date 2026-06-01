<?php
$images = get_sub_field('gallery');
$size   = get_sub_field('media_size') ?: 'd-whole';

if ( ! $images ) return;
?>
<div class="d-flex flex-row<?= $size !== 'd-whole' ? ' v-center' : ''; ?>">
  <div class="slider-container <?= $size; ?> m-whole">
    <div class="slider-track">
      <button class="slider-nav slider-nav-prev" aria-label="Previous slide">
        <svg width="27" height="46" viewBox="0 0 27 46" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M25.1965 1.08765L2.19653 22.9289L25.1965 44.0876" stroke="currentColor" stroke-width="2"/>
        </svg>
      </button>
      <div class="swiper slider-module">
        <div class="swiper-wrapper">
          <?php foreach ($images as $image): ?>
            <div class="swiper-slide">
              <?= wp_get_attachment_image($image['ID'], 'full'); ?>
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
