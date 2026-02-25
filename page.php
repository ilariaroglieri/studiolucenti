<?php get_header(); ?>

<section class="content" id="content-page">
  <div class="container">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

      <section id="text-section" class="d-flex center full-height">
        <div class="d-flex flex-row">
          <div class="d-10-twelfth m-whole">
            <div class="wysiwyg s-medium">
              <?php the_content(); ?>
            </div>
          </div>
        </div>
      </section>
      
    <?php endwhile; else: ?>

      <h2>Woops...</h2>
      <p>Sorry, no content found.</p>

    <?php endif; ?>
  </div>
</section>

<?php get_footer(); ?>