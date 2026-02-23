	<footer id="footer">
		<div class="container spacing-p-t-1 spacing-p-b-1">
			<div class="d-flex flex-row m-column">
				<div class="d-two-thirds m-whole">
					<div id="contact-details" class="d-flex d-column space-between">
						<div class="wysiwyg s-medium">
							<p><?php bloginfo('name'); ?></p>

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