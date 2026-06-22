<?php get_header(); ?>

<section role="main" class="content" id="content-home">
  <div class="container">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

      <section id="intro-text" class="d-flex center full-height">
        <div class="d-flex flex-row">
          <div class="d-10-twelfth m-whole">
            <div class="text-element-lines wysiwyg s-medium">
              <?php the_content(); ?>
            </div>
          </div>
        </div>
      </section>

      

      <?php
        $hasReel = get_field('show_video_reel');
        $reel = get_field('video_reel');

        if ($hasReel == 1): ?>

        <section id="video-reel">
          <div class="d-flex flex-row">
            <div class="d-whole">
              <div class="video-container">
                <video id="hls-video" playsinline loop autoplay>
                  <source src="<?= esc_url($reel); ?>?>">
                </video>
              </div>
            </div>
          </div>
        </section>

      <?php endif; ?>
      
      <?php 
        $rows = get_field('featured_projects');

        if ( $rows ):
          // shuffle($rows);
        ?>
        <div class="d-flex flex-row wrap v-center">
          <?php foreach ( $rows as $row ) :
            $project = $row['featured_project'];
            $home_width = $row['override_width'];

            if ( $project ) :
              $post = $project;
              setup_postdata( $post );

              displayGridProject($home_width);

              wp_reset_postdata();

            endif;
          endforeach; ?>
        </div>

        <div class="d-flex flex-row v-center spacing-p-t-2 spacing-p-b-2">
          <div class="d-whole t-center">
            <a href="<?= esc_url( get_permalink( get_option('page_for_posts') ) ); ?>" class="s-regular">
              See all Projects
            </a>
          </div>
        </div>

      <?php endif; ?>

    <?php endwhile; else: ?>

      <h2>Woops...</h2>
      <p>Sorry, no content found.</p>

    <?php endif; ?>
  </div>
</section>

<?php get_footer(); ?>