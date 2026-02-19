<?php get_header(); ?>

<section class="content" id="content-single">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      <?php the_content(); ?>
    </article>

  <?php endwhile; else: ?>

    <h2>Woops...</h2>
    <p>Sorry, no posts found.</p>

  <?php endif; ?>

</section>

<div class="navigation">
  <div class="navi previous"><?php previous_post_link( '%link','⟵', false ); ?></div>
  <div class="navi next"><?php next_post_link( '%link','⟶', false ); ?></div>
</div>

<?php get_footer(); ?>