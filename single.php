<?php get_header(); ?>

<div role="main" class="content" id="content-single">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

    <project id="post-<?php the_ID(); ?>" <?php post_class('container-fluid'); ?>>

     <div class="container">
        <div class="d-flex flex-row">
          <div class="d-whole">
            <h1 class="text-element s-medium spacing-b-half">
              <?php the_title(); ?>
              <?php if ( current_user_can( 'edit_post', get_the_ID() ) ): ?>
                <a href="<?= esc_url( get_edit_post_link() ); ?>" class="edit-post-link" title="Edit project">
                  <span class="dashicons dashicons-edit"></span>
                </a>
              <?php endif; ?>
            </h1>
          </div>
        </div>
      </div>

      <?php
        $featured_medium       = get_field('hero_medium');
        $featured_embed        = get_field('hero_video_embed');
        $video_poster          = get_field('hero_video_poster');
        $hero_background_video = get_field('hero_background_video');
        $hero_aspect_ratio     = get_field('hero_aspect_ratio') ?: '16/9';
        $hero_bg_color         = get_field('hero_background_color');
        $medium_id             = get_medium_id_from_acf($featured_medium);
        $is_vertical           = in_array($hero_aspect_ratio, ['4/5', '3/4']);

        if ($hero_background_video) {
          $hero_video_class = 'bg-video';
          $hero_video_attrs = 'autoplay loop muted playsinline';
        } else {
          $hero_video_class = 'hls-video';
          $hero_video_attrs = 'controls playsinline';
        }

        $ar_vars = '';
        if ($is_vertical) {
          [$ar_w, $ar_h] = explode('/', $hero_aspect_ratio);
          $ar_vars = " --ar-w: {$ar_w}; --ar-h: {$ar_h};";
        }

        if ($medium_id || $featured_embed):
      ?>

        <section id="hero-section" class="container-fluid spacing-b-2<?php if ($hero_bg_color): ?> has-hero-bg<?php endif; ?>"<?php if ($hero_bg_color): ?> style="background-color: <?= esc_attr($hero_bg_color); ?>"<?php endif; ?>>
          <div class="container">
            <div class="d-flex flex-row">
              <div class="d-whole">
                <?php if ($featured_embed): ?>
                  <div class="hero-video-outer<?= $is_vertical ? ' hero-vertical-outer' : ''; ?>">
                    <div class="video-container<?= $is_vertical ? ' hero-vertical' : ''; ?>" style="aspect-ratio: <?= esc_attr($hero_aspect_ratio); ?><?= $ar_vars; ?>">
                      <video class="<?= $hero_video_class; ?>" <?= $hero_video_attrs; ?><?php if ($video_poster): ?> poster="<?= esc_url($video_poster); ?>"<?php endif; ?>>
                        <source src="<?= esc_url($featured_embed); ?>">
                      </video>
                    </div>
                  </div>
                <?php else:
                  render_media($medium_id, 12, true);
                endif; ?>
              </div>
            </div>
          </div>
        </section>

      <?php endif; ?>

      <section id="text-section" class="container spacing-t-2 spacing-b-3 spacing-m-b-2">
        <div class="d-flex flex-row m-column">
          <div class="d-7-twelfth m-whole">
            <div class="text-element-lines wysiwyg s-regular">
              <?php the_content(); ?>
            </div>
          </div>

          <div class="spacer d-1-twelfth m-hidden"></div>

          <div class="d-one-third m-whole spacing-m-t-2">
            <?php
              $year = get_post_terms($post->ID, 'project_year'); 
              $client = get_post_terms($post->ID, 'project_client');
            ?>
            
            <?php if ($year): ?>
              <div class="text-element project-info">
                <p class="s-xxsmall uppercase">Year</p>
                <h3 class="s-small"><?= $year; ?></h3>
              </div>
            <?php endif; ?>

            <?php if ($client): ?>
              <div class="text-element project-info">
                <p class="s-xxsmall uppercase">Client</p>
                <h3 class="s-small"><?= $client; ?></h3>
              </div>
            <?php endif; ?>

            <?php if( have_rows('credits') ): while ( have_rows('credits') ) : the_row(); ?>

              <div class="text-element project-info">
                <p class="s-xxsmall uppercase"><?php the_sub_field('role'); ?></p>
                <h3 class="s-small"><?php the_sub_field('name'); ?></h3>
              </div>

            <?php endwhile; endif; ?>
          </div>
        </div>
      </section>

      <!-- flexible acf -->
      <?php if ( have_rows('project_modules') ): ?>
        <div class="modules-container">
          <?php while ( have_rows('project_modules') ) : the_row();
            $bndCol     = get_sub_field('dark_background');
            $moduleType = match (get_row_layout()) {
              'text_row'       => 'text-module',
              'media_text_row' => 'media-text-module',
              'video_row'      => 'video-module',
              'slider_row'     => 'slider-module',
              default          => 'media-module',
            };
            $layoutFile = str_replace('_', '-', get_row_layout());
          ?>
            <div data-type="<?= $moduleType; ?>" class="container-fluid content-module spacing-t-3 spacing-b-3 spacing-m-t-0 spacing-m-b-0<?php if ($bndCol == 1): ?> dark<?php endif; ?>">
              <div class="container">
                <?php get_template_part('template-parts/modules/' . $layoutFile); ?>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php endif; ?>
      
    </project>

  <?php endwhile; else: ?>

    <h2>Woops...</h2>
    <p>Sorry, no posts found.</p>

  <?php endif; ?>

  <?php 
    $next = get_circular_adjacent_post(get_the_ID(), '', '', 'next');
    $prev = get_circular_adjacent_post(get_the_ID(), '', '', 'prev');
  ?>

  <div class="navi container spacing-t-3">
    <div class="d-flex flex-row m-column">
      <?php if ($next): 
        $title = get_the_title($next->ID);
        $permalink = get_the_permalink($next->ID);
        $featured_medium = get_field('featured_medium', $next->ID);
        $medium_id = get_medium_id_from_acf($featured_medium); 
        ?>
        <project id="post-<?php the_ID(); ?>" class="project d-half m-whole p-relative spacing-b-3 spacing-t-3 spacing-m-t-2 spacing-m-b-2">
          <a class="p-absolute overall" href="<?= $permalink; ?>"></a>
          <h3 class="s-xxsmall uppercase spacing-b-tiny">Previous</h3>
          <h2 class="project-title s-regular spacing-b-half"><?= esc_html($title); ?></h2>
          <?php render_media($medium_id, 6, false); ?>
        </project>
        
      <?php endif; ?>

      <?php if ($prev): 
        $title = get_the_title($prev->ID);
        $permalink = get_the_permalink($prev->ID);
        $featured_medium = get_field('featured_medium', $prev->ID);
        $medium_id = get_medium_id_from_acf($featured_medium); 
        ?>
        <project id="post-<?php the_ID(); ?>" class="project d-half m-whole p-relative spacing-b-3 spacing-t-3 spacing-m-t-2 spacing-m-b-2">
          <a class="p-absolute overall" href="<?= $permalink; ?>"></a>
          <h3 class="s-xxsmall uppercase spacing-b-tiny">Next</h3>
          <h2 class="project-title s-regular spacing-b-half"><?= esc_html($title); ?></h2>
          <?php render_media($medium_id, 6, false); ?>
        </project>
        
      <?php endif; ?>
    </div>
  </div>

</div>


<?php get_footer(); ?>