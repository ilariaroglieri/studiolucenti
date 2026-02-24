<?php get_header(); ?>

<section class="content" id="content-work">
  <div class="container">
    <?php if ( have_posts() ) : ?>
      <div class="d-flex flex-row wrap v-center">
        <?php while ( have_posts() ) : the_post();
        
        displayGridProject(null);
      
        endwhile; ?>
      </div>
    <?php else: ?>

      <h2>Woops...</h2>
      <p>Sorry, no content found.</p>

    <?php endif; ?>
  </div>
</section>

<?php get_footer(); ?>