<?php get_header(); ?>

<div class="content" id="content-single">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

    <project id="post-<?php the_ID(); ?>" <?php post_class('container-fluid'); ?>>

      <?php 
        $featured_medium = get_field('hero_medium');
        $medium_id   = get_medium_id_from_acf($featured_medium); 

        if ($medium_id):
      ?>
        <section id="hero-section" class="container spacing-b-2">
          <div class="d-flex flex-row">
            <div class="d-whole">
              <h1 class="s-medium spacing-b-half"><?php the_title(); ?></h1>
              <?php render_media($medium_id, 12, true); ?>
            </div>
          </div>
        </section>

      <?php endif; ?>

      <section id="text-section" class="container spacing-t-2 spacing-b-3">
        <div class="d-flex flex-row m-column">
          <div class="d-7-twelfth m-whole">
            <div class="wysiwyg s-regular">
              <?php the_content(); ?>
            </div>
          </div>

          <div class="spacer d-1-twelfth m-hidden"></div>

          <div class="d-one-third m-whole">
            <div class="wysiwyg s-regular">
              <?php
                $year = get_post_terms($post->ID, 'project_year'); 
                $client = get_post_terms($post->ID, 'project_client');
              ?>
              
              <?php if ($year): ?>
                <div class="project-info">
                  <p class="s-xxsmall uppercase">Year</p>
                  <h3 class="s-small"><?= $year; ?></h3>
                </div>
              <?php endif; ?>

              <?php if ($client): ?>
                <div class="project-info">
                  <p class="s-xxsmall uppercase">Client</p>
                  <h3 class="s-small"><?= $client; ?></h3>
                </div>
              <?php endif; ?>

              <?php if( have_rows('credits') ): while ( have_rows('credits') ) : the_row(); ?>

                <div class="project-info">
                  <p class="s-xxsmall uppercase"><?php the_sub_field('role'); ?></p>
                  <h3 class="s-small"><?php the_sub_field('name'); ?></h3>
                </div>

              <?php endwhile; endif; ?>
            </div>
          </div>
        </div>
      </section>

      <!-- flexible acf -->
      <?php
      if( have_rows('project_modules') ): while ( have_rows('project_modules') ) : the_row();
        $bndCol = get_sub_field('dark_background');
        $moduleType = match (get_row_layout()) {
          'text_row' => 'text-module',
          'media_text_row' => 'media-text-module',
          default => 'media-module',
        };
        ?>

        <div data-type="<?= $moduleType; ?>" class="container-fluid content-module spacing-p-t-3 spacing-p-b-3<?php if ($bndCol == 1): ?> dark<?php endif; ?>">
          <div class="container">
            <?php if( get_row_layout() == 'text_row' ):
              $text = get_sub_field('text');
              $textAlignment = get_sub_field('text_alignment');
            ?>
            <div class="d-flex flex-row <?= $textAlignment; ?>">
              <div class="d-two-thirds t-whole">
                <div class="wysiwyg s-regular">
                  <?= $text; ?>
                </div>
              </div>
            </div>


            <?php elseif( get_row_layout() == 'media_text_row' ): 
              $text = get_sub_field('text');
              $alignment = get_sub_field('media_alignment');
              $medium = get_sub_field('all_row_media');
              $medium_id   = get_medium_id_from_acf($medium); 
            ?>
              <div class="d-flex flex-row <?= $alignment; ?>">
                <div class="d-half t-whole">
                  <div class="wysiwyg s-regular">
                    <?= $text; ?>
                  </div>
                </div>
                <div class="d-half t-whole">
                  <?php render_media($medium_id, 6); ?>
                </div>
              </div>

            <?php elseif( get_row_layout() == 'media_row' ): 
              $media = get_sub_field('all_row_media');
              $alignment = get_sub_field('media_alignment');
              $twoSizes = get_sub_field('two_media_sizes');
              $singleSize = get_sub_field('one_media_size');
              $count = count($media);
              ?>

              <div class="d-flex flex-row <?= $alignment; ?> m-column">
                <?php foreach ($media as $index => $m):
                  // DEFAULT
                  $class = 'd-half';
                  $imgSize = 6;

                  if ($count === 1) {
                    $class = $singleSize;
                    $imgSize = 12;

                  } elseif ($count === 2) {
                    if ($twoSizes === 'd-half') {
                      $class = 'd-half';
                      $imgSize = 6;
                    } elseif ($twoSizes === 'd-one-third') {
                      $class = ($index === 0) ? 'd-5-twelfth' : 'd-7-twelfth';
                      $imgSize = ($index === 0) ? 4 : 6;
                    } elseif ($twoSizes === 'd-two-thirds') {
                      $class = ($index === 0) ? 'd-7-twelfth' : 'd-5-twelfth';
                      $imgSize = ($index === 0) ? 6 : 4;
                    }
                  } elseif ($count === 3) {
                    $class = 'd-one-third';
                    $imgSize = 4;
                  }

                  $medium_id = get_medium_id_from_acf($m);
                ?>

                <div class="<?= $class; ?> m-whole">
                  <?php render_media($medium_id, $imgSize); ?>
                </div>

                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endwhile; endif; ?>


    </project>

  <?php endwhile; else: ?>

    <h2>Woops...</h2>
    <p>Sorry, no posts found.</p>

  <?php endif; ?>

</div>

<div class="navigation">
  <div class="navi previous"><?php previous_post_link( '%link','⟵', false ); ?></div>
  <div class="navi next"><?php next_post_link( '%link','⟶', false ); ?></div>
</div>

<?php get_footer(); ?>