<?php get_header(); ?>

<div class="content" id="content-single">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

    <project id="post-<?php the_ID(); ?>" <?php post_class('container-fluid'); ?>>

      <?php 
        $featured_medium = get_field('hero_medium');
        $medium_id   = get_medium_id_from_acf($featured_medium); 

        if ($medium_id):
      ?>
        <section id="hero-section" class="container">
          <div class="d-flex flex-row">
            <div class="d-whole">
              <?php render_media($medium_id, 12, true); ?>
            </div>
          </div>
        </section>

      <?php endif; ?>

      <section id="text-section" class="container">
        <div class="d-flex flex-row m-column">
          <div class="d-two-thirds m-whole">
            <div class="wysiwyg s-regular">
              <?php the_content(); ?>
            </div>
          </div>

          <div class="d-one-third m-whole">
            <div class="wysiwyg s-regular">
              <?php the_content(); ?>
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