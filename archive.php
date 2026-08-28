<?php get_header(); ?>

<section role="main" class="content" id="content-archive">
  <div class="container">
    <?php if ( have_posts() ) : ?>

      <div class="d-flex flex-row wrap v-center">
        <?php while ( have_posts() ) : the_post();
          displayGridProject(null);
        endwhile; ?>
      </div>

    <?php else: ?>

      <h2>Woops...</h2>
      <p>Sorry, no posts found.</p>

    <?php endif; ?>
  </div>
</section>

<?php get_footer(); ?>
