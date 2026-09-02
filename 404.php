<?php get_header(); ?>

<section role="main" class="content" id="content-404">
  <div class="container">
    <section id="error-404" class="d-flex center">
      <div class="d-flex flex-row">
        <div class="d-10-twelfth m-whole">

          <?php // La label sopra e il valore sotto: è la stessa impaginazione
                // della meta bar delle schede progetto. Qui porta anche il peso
                // semantico, perché "404" da solo non dice niente a chi legge
                // con uno screen reader. ?>
          <p class="mono s-xxsmall spacing-b-half">Page not found</p>

          <h1 id="error-code" class="s-large spacing-b-1">404</h1>

          <p class="s-medium spacing-b-2 error-line">
            This frame didn&rsquo;t make the final cut.
          </p>

          <p class="s-regular">
            <a href="<?= esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" class="link-line">Back to the work</a>
            <span class="error-sep">&middot;</span>
            <a href="<?= esc_url( home_url( '/' ) ); ?>" class="link-line">Home</a>
          </p>

        </div>
      </div>
    </section>
  </div>
</section>

<?php get_footer(); ?>
