<?php get_header(); ?>

<section role="main" class="content" id="content-home">
  <div class="container">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

      <section id="intro-text" class="d-flex center">
        <div class="d-flex flex-row">
          <div class="d-10-twelfth m-whole">
            <div class="text-element-lines wysiwyg s-medium">
              <?php the_content(); ?>
            </div>
          </div>
        </div>
      </section>

      

      <?php
        $hasReel   = get_field('show_video_reel');
        $reel      = get_field('video_reel');

        // Il flag da solo non basta: con l'interruttore acceso e il campo URL
        // vuoto usciva un <video> senza sorgente, cioè un buco alto mezzo schermo.
        if ($hasReel == 1 && $reel):

          // Vimeo il primo frame non lo espone: `get_video_poster()` legge
          // l'immagine in evidenza di un allegato, e qui di allegato non ce n'è
          // nessuno — il reel è un URL HLS. Da qui il campo ACF a fianco.
          //
          // L'URL viene dalla stessa funzione che header.php usa per il
          // `<link rel="preload">`: se le due divergono il preload non viene
          // riscattato e il poster si scarica due volte.
          $reel_poster = studiolucenti_reel_poster_url();
        ?>

        <section id="video-reel">
          <div class="d-flex flex-row">
            <div class="d-whole">
              <div class="video-container">
                <?php // `bg-video`, non `hls-video`: il reel è un loop ambientale,
                      // non un film. La barra di Plyr era già spenta da CSS su
                      // .home, quindi la libreria non ci faceva niente — se non
                      // metterci sopra il cerchio di play, che resta in campo
                      // finché lo stream non parte e si legge come un video fermo.
                      // Senza Plyr resta il percorso di hls.js e basta. ?>
                <video <?= render_video_attrs(['class' => 'bg-video', 'poster' => $reel_poster]); ?>>
                  <source src="<?= esc_url($reel); ?>">
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
            <a href="<?= esc_url( get_permalink( get_option('page_for_posts') ) ); ?>" class="s-regular link-line">
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