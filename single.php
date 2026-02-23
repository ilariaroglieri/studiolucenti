<?php get_header(); ?>

<div class="content" id="content-single">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

    <project id="post-<?php the_ID(); ?>" <?php post_class('container-fluid'); ?>>

      <?php 
        $featured_medium = get_field('hero_medium');
        $medium_id   = get_medium_id_from_acf($featured_medium); 

        if ($medium_id):
      ?>
        <section id="hero-section" class="container spacing-b-1">
          <div class="d-flex flex-row">
            <div class="d-whole">
              <h1 class="s-medium spacing-b-half"><?php the_title(); ?></h1>
              <?php render_media($medium_id, 12, true); ?>
            </div>
          </div>
        </section>

      <?php endif; ?>

      <section id="text-section" class="container">
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