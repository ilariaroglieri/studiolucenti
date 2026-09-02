<?php
/*
 * Template Name: About
 */
?>

<?php get_header(); ?>

<section role="main" class="content" id="content-page">
  <div class="container">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

      <section id="text-section" class="spacing-t-8">
        <div class="d-flex flex-row">
          <div class="d-10-twelfth m-whole">
            <div class="text-element-lines wysiwyg s-medium">
              <?php the_content(); ?>
            </div>
          </div>
        </div>
      </section>

      <?php // Respiro in fondo alla pagina: senza, l'ultima riga cadeva sul
            //  bordo di `.content` e il footer cominciava a scoprirsi attaccato
            //  al testo. Tre cose da sapere:
            //
            //  - **padding, non margine.** Un margine qui collassa attraverso
            //    `.container` e `.content` e finisce per confondersi con il
            //    `margin-bottom: var(--footer-height)` di `.content`, che è più
            //    grande: si scrive e non succede niente. Stesso inciampo già
            //    annotato per l'intro della home.
            //  - **24px su desktop, 48 su mobile**, e non è un capriccio: a
            //    chiudere la pagina sono due elementi diversi. Su desktop è la
            //    colonna Career, la più alta, e la sua ultima voce porta già
            //    48px suoi — 24 in più fanno i 72 che hanno /work/ e le schede.
            //    Sotto i 640px le colonne si impilano e a chiudere è la lista
            //    dei clienti, che di margine suo non ne ha: lì i 48 servono
            //    tutti.
            //  - le due misure di partenza, prese sul sito: 48px sotto Career a
            //    1440, **zero** sotto l'ultimo cliente a 375. ?>
      <div id="infos" class="spacing-t-6 spacing-p-b-1 spacing-m-p-b-2 d-flex flex-row wrap">
        <?php $maintitle = get_field('info_main_title'); ?>
        <?php if ( have_rows( 'infos' ) ) : ?>
          
          <div id="info-list" class="d-two-thirds t-half m-whole">
            <?php if ($maintitle): ?>
              <?php // "Career" da solo non dice di chi: la lista sono i passi
                    //  precedenti del fondatore, non dello studio, che esiste
                    //  dal 2024. Il nome è il valore, quindi resta in PP Mori:
                    //  la mono è la scala di servizio, e qui fa la label sopra.
                    //  Stesso rapporto — 6px — che c'è fra l'anno e il testo di
                    //  ogni voce qui sotto.
                    //
                    //  Non è un campo ACF di proposito: un campo nuovo chiede un
                    //  Sync, e senza Sync `get_field()` torna null in silenzio e
                    //  il sottotitolo sparirebbe senza dirlo. Stesso criterio del
                    //  colophon nel footer. ?>
              <div id="info-main-title" class="text-element spacing-b-1">
                <h2 class="mono s-xxsmall spacing-b-tiny"><?= $maintitle; ?></h2>
                <p class="s-regular">Ignazio Lucenti</p>
              </div>
            <?php endif; ?>

            <?php  while ( have_rows( 'infos' ) ) : the_row();
              $title = get_sub_field( 'info_title' );
              $text = get_sub_field( 'info_text' ); 
            ?>
              <div class="text-element info spacing-b-2">
                <?php if ($title): ?>
                  <h3 class="mono s-xxsmall spacing-b-tiny"><?= $title; ?></h3>
                <?php endif; ?>

                <?php if ($text): ?>
                  <div class="wysiwyg s-regular"><?= $text; ?></div>
                <?php endif; ?>
              </div>
            <?php endwhile; ?>

          </div>
        <?php endif; ?>

        <?php 
        $terms = get_field('selected_clients');
        if( $terms ): ?>

          <div id="clients-list" class="d-one-third t-half m-whole">
            <div id="clients-main-title" class="text-element spacing-b-1">
              <h2 class="mono s-xxsmall">Selected clients</h2>
            </div>
            <?php foreach( $terms as $term ): ?>
              <p class="s-regular text-element"><?php echo esc_html( $term->name ); ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      
    <?php endwhile; else: ?>

      <h2>Woops...</h2>
      <p>Sorry, no content found.</p>

    <?php endif; ?>
  </div>
</section>

<?php get_footer(); ?>