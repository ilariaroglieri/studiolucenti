	<footer id="footer">
		<div class="container full-height spacing-p-t-1 spacing-p-b-1 d-flex d-column">
			<div class="d-flex flex-row spacing-b-3">
				<div class="d-whole">
					<p class="s-big"><?php bloginfo('name'); ?></p>
				</div>
			</div>

			<div class="d-flex flex-row grow m-column">
				<div class="d-two-thirds m-whole d-flex">
					<div id="contact-details" class="d-flex d-column space-between grow">
						<div class="wysiwyg s-medium">
							<?php the_field('contact_details', 'option'); ?>
						</div>
						<p class="s-xsmall">© <?php the_time('Y'); ?> </p>
					</div>
				</div>

				<div class="d-one-third m-whole d-flex d-column">
					<?php if( have_rows('socials','option') ): while ( have_rows('socials','option') ) : the_row(); 
						$link = get_sub_field('social_link'); ?>

            <a class="s-small" href="<?= $link['url']; ?>" target="_blank"><?= $link['title']; ?></a>

          <?php endwhile; endif; ?>
				</div>
			</div>
		</div>
		
	</footer>
	 

<?php wp_footer(); ?>

</body>
</html>