<?php get_header(); ?>

<section class="content" id="content-page">
  <div class="container">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

      <section id="text-section" class="spacing-t-8">
        <div class="d-flex flex-row">
          <div class="d-10-twelfth m-whole">
            <div class="wysiwyg s-medium">
              <?php the_content(); ?>
            </div>
          </div>
        </div>
      </section>

      <div id="infos" class="spacing-t-6 d-flex flex-row wrap">
        <?php $maintitle = get_field('info_main_title'); ?>
        <?php if ( have_rows( 'infos' ) ) : ?>
          
          <div id="info-list" class="d-two-thirds t-half m-whole">
            <?php if ($maintitle): ?>
              <div id="info-main-title" class="spacing-b-1">
                <h2 class="s-xxsmall uppercase"><?= $maintitle ?></h2>
              </div>
            <?php endif; ?>

            <?php  while ( have_rows( 'infos' ) ) : the_row();
              $title = get_sub_field( 'info_title' );
              $text = get_sub_field( 'info_text' ); 
            ?>
              <div class="info spacing-b-2">
                <?php if ($title): ?>
                  <h3 class="s-xxsmall uppercase spacing-b-tiny"><?= $title; ?></h3>
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
            <div id="clients-main-title" class="spacing-b-1">
              <h2 class="s-xxsmall uppercase">Selected clients</h2>
            </div>
            <?php foreach( $terms as $term ): ?>
              <p class="s-regular"><?php echo esc_html( $term->name ); ?></p>
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