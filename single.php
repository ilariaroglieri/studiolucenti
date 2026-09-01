<?php get_header(); ?>

<div role="main" class="content" id="content-single">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

    <project id="post-<?php the_ID(); ?>" <?php post_class('container-fluid'); ?>>

      <?php
        $featured_medium       = get_field('hero_medium');
        $featured_embed        = get_field('hero_video_embed');
        $video_poster          = get_field('hero_video_poster');
        $hero_background_video = get_field('hero_background_video');
        $hero_aspect_ratio     = get_field('hero_aspect_ratio') ?: '16/9';
        $hero_bg_color         = get_field('hero_background_color');
        $medium_id             = get_medium_id_from_acf($featured_medium);
        $is_vertical           = in_array($hero_aspect_ratio, ['4/5', '3/4', '1/1']);

        // L'hero parte SEMPRE come loop muto in autoplay. Sul film interattivo
        // Plyr c'è già ma la sua barra resta nascosta (stato `.hero-idle`):
        // si vede solo il cerchio al centro. Al click compaiono insieme audio,
        // allargamento e controlli — vedi initHeroIdleOverlay() in custom.js.
        // `hero_background_video` resta per i loop puramente ambientali, che
        // non ricevono né Plyr né cerchio: loop muto per sempre.
        $hero_video_attrs = render_video_attrs([
          'autoplay' => true,
          'controls' => ! $hero_background_video,
          'hero'     => true,
          'poster'   => $video_poster,
          'class'    => $hero_background_video ? 'bg-video' : 'hls-video hero-video',
        ]);

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
                    <?php // data-vt-media: lato d'arrivo della transizione FLIP, speculare
                          // alla thumbnail in griglia. L'hero da embed non passa da
                          // render_media(), quindi l'aggancio va messo a mano ?>
                    <div class="video-container<?= $is_vertical ? ' hero-vertical' : ''; ?>" data-vt-media style="aspect-ratio: <?= esc_attr($hero_aspect_ratio); ?><?= $ar_vars; ?>">
                      <video <?= $hero_video_attrs; ?>>
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

      <div class="container">
        <div class="d-flex flex-row">
          <div class="d-whole">
            <?php // Niente `text-element` qui: quella classe lo faceva entrare nel
                  // reveal a blocco di `.single .flex-row`, e il titolo ha il suo
                  // — parola per parola, in scroll.js. Due animazioni sullo stesso
                  // elemento si sovrappongono e vince l'ultima che scrive. ?>
            <h1 class="s-medium spacing-b-half">
              <?php // Il testo va isolato dal link di modifica: SplitText divide
                    // tutto quello che trova dentro l'elemento, e l'icona
                    // dell'admin finirebbe fra le parole del titolo. ?>
              <span class="project-title-words"><?php the_title(); ?></span>
              <?php if ( current_user_can( 'edit_post', get_the_ID() ) ): ?>
                <a href="<?= esc_url( get_edit_post_link() ); ?>" class="edit-post-link" title="Edit project">
                  <span class="dashicons dashicons-edit"></span>
                </a>
              <?php endif; ?>
            </h1>
          </div>
        </div>
      </div>

      <section id="text-section" class="container spacing-t-1 spacing-b-3 spacing-m-b-2">
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
                <p class="mono s-xxsmall">Year</p>
                <h3 class="s-small"><?= $year; ?></h3>
              </div>
            <?php endif; ?>

            <?php if ($client): ?>
              <div class="text-element project-info">
                <p class="mono s-xxsmall">Client</p>
                <h3 class="s-small"><?= $client; ?></h3>
              </div>
            <?php endif; ?>

            <?php if( have_rows('credits') ): while ( have_rows('credits') ) : the_row(); ?>

              <div class="text-element project-info">
                <p class="mono s-xxsmall"><?php the_sub_field('role'); ?></p>
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
            <?php // spacing-m-t-0/spacing-m-b-0 non ci vanno: azzerano il margine su
                  // OGNI modulo da mobile in giù, non solo su quelli scuri consecutivi
                  // per cui esiste il compenso in padding — vedi .content-module.dark
                  // in style.scss. Con quella coppia, un modulo video o testo qualsiasi
                  // restava incollato al contenuto sopra e sotto. ?>
            <div data-type="<?= $moduleType; ?>" class="container-fluid content-module spacing-t-3 spacing-b-3<?php if ($bndCol == 1): ?> dark<?php endif; ?>">
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
        <project id="post-<?= (int) $next->ID; ?>" class="project d-half m-whole p-relative spacing-b-3 spacing-t-3 spacing-m-t-2 spacing-m-b-2">
          <a class="p-absolute overall" href="<?= esc_url($permalink); ?>" aria-label="<?= esc_attr($title); ?>"></a>
          <h3 class="mono s-xxsmall spacing-b-tiny">Previous</h3>
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
        <project id="post-<?= (int) $prev->ID; ?>" class="project d-half m-whole p-relative spacing-b-3 spacing-t-3 spacing-m-t-2 spacing-m-b-2">
          <a class="p-absolute overall" href="<?= esc_url($permalink); ?>" aria-label="<?= esc_attr($title); ?>"></a>
          <h3 class="mono s-xxsmall spacing-b-tiny">Next</h3>
          <h2 class="project-title s-regular spacing-b-half"><?= esc_html($title); ?></h2>
          <?php render_media($medium_id, 6, false); ?>
        </project>
        
      <?php endif; ?>
    </div>
  </div>

</div>


<?php get_footer(); ?>