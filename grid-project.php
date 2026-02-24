<?php
  $home_width;
  $featured_medium = get_field('featured_medium');
  $featured_medium_size = get_field('featured_medium_size');

  $currSize = ($home_width !== null) ? $home_width : $featured_medium_size;
  $medium_id = get_medium_id_from_acf($featured_medium); 
  $width = match ($featured_medium_size) {
    'd-whole' => 12,
    'd-10-twelfth' => 12,
    'd-two-thirds' => 12,
    'd-half' => 6,
    'd-one-third' => 4,
    default => 6,
  };
?>

<project id="post-<?php the_ID(); ?>" class="<?= $featured_medium_size; ?> p-relative spacing-b-3 spacing-t-3">
  <a class="p-absolute overall" href="<?php the_permalink(); ?>"></a>
  <h2 class="project-title s-regular spacing-b-half"><?php the_title(); ?></h2>
  <?php render_media($medium_id, $width, false); ?>
</project>