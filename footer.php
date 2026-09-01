	<?php
		// Il footer è `fixed` dietro `.content`: scrollando si scopre dal basso
		// verso l'alto. Quindi si legge dal basso: la prima fascia che appare è
		// l'ultima riga del markup, e per quello è il rail di metadati — la cosa
		// più densa e l'unica viva — a stare sul bordo. Tutto è ancorato in basso
		// (`.flex-end`): il vuoto resta sopra, dove legge come respiro.
		$footer_intro  = trim( (string) get_field( 'footer_intro', 'option' ) );
		$contact_email = sanitize_email( (string) get_field( 'contact_email', 'option' ) );
		$availability  = trim( (string) get_field( 'availability_status', 'option' ) );

		// L'ora del server non è l'ora di Milano: il fuso va imposto qui, non
		// dedotto. È solo il valore di partenza — dopo un secondo lo riscrive il
		// JS — ma senza JS resta comunque un'ora giusta invece di un trattino.
		$milan_now = new DateTimeImmutable( 'now', new DateTimeZone( 'Europe/Rome' ) );
	?>

	<footer role="contentinfo" id="contact">
		<div class="container full-height d-flex d-column flex-end spacing-p-t-1 spacing-p-b-1">

			<?php // 4 · Cosa fa lo studio, in prosa. È l'ultima fascia che si scopre e
			      // la sola prosa del footer: dopo aver visto il lavoro, la frase che
			      // gli dà un nome. È anche l'unico punto del sito dove i termini di
			      // settore stanno in un testo indicizzabile — la pagina servizi è
			      // stata tolta da Decisioni → D6, e questa ne è la mitigazione. ?>
			<?php if ( $footer_intro ) : ?>
				<div class="d-flex flex-row spacing-b-4 footer-intro">
					<div class="d-two-thirds m-whole">
						<p class="s-medium"><?= esc_html( $footer_intro ); ?></p>
					</div>
				</div>
			<?php endif; ?>

			<?php // 3 · L'elemento a grande scala è l'email, non il nome dello studio:
			      // il wordmark è già fisso nell'header, e il footer serve a farsi
			      // contattare. Senza email non si stampa un titolo vuoto. ?>
			<?php if ( $contact_email ) : ?>
				<div class="d-flex flex-row spacing-b-4 footer-email">
					<div class="d-whole">
						<p class="s-large"><a href="mailto:<?= antispambot( $contact_email ); ?>"><?= antispambot( $contact_email ); ?></a></p>
					</div>
				</div>
			<?php endif; ?>

			<?php // 2 · Contatti e social sulla stessa riga. ?>
			<div class="d-flex flex-row m-column spacing-b-3 footer-contacts">
				<div class="d-two-thirds m-whole">
					<div class="wysiwyg s-small">
						<?php the_field( 'contact_details', 'option' ); ?>
					</div>
				</div>

				<div class="d-one-third m-whole d-flex wrap footer-socials">
					<?php if ( have_rows( 'socials', 'option' ) ) : while ( have_rows( 'socials', 'option' ) ) : the_row();
						$link = get_sub_field( 'social_link' );
						if ( empty( $link['url'] ) ) { continue; } ?>

						<a class="s-small" href="<?= esc_url( $link['url'] ); ?>" target="<?= esc_attr( $link['target'] ?: '_blank' ); ?>" rel="noopener noreferrer"><?= esc_html( $link['title'] ); ?></a>

					<?php endwhile; endif; ?>
				</div>
			</div>

			<?php // 1 · Il rail: la prima cosa che si scopre scrollando. Va tenuto
			      // denso, o si sposta il vuoto invece di riempirlo. ?>
			<div class="d-flex flex-row t-column footer-rail">

				<div class="d-one-third t-whole m-whole">
					<p class="mono s-xxsmall">
						<time id="footer-clock" datetime="<?= esc_attr( $milan_now->format( 'H:i:s' ) ); ?>"><?= esc_html( $milan_now->format( 'H:i:s' ) ); ?></time> Milan
					</p>
					<?php if ( $availability ) : ?>
						<p class="mono s-xxsmall footer-availability"><?= esc_html( $availability ); ?></p>
					<?php endif; ?>
				</div>

				<?php // Il colophon è la nota tipografica dell'oggetto — carattere,
				      // stack, anno — non l'elenco di chi ci ha lavorato. L'anno è
				      // fisso: dice quando è stata costruita questa versione, non
				      // che anno è adesso. Quello lo dice il copyright, qui sotto. ?>
				<div class="d-one-third t-whole m-whole">
					<p class="mono s-xxsmall footer-colophon">
						Set in PP Mori and DM Mono<br>
						Built with WordPress, GSAP and Lenis<br>
						Designed and coded in-house · Milan, 2026
					</p>
				</div>

				<div class="d-one-third t-whole m-whole footer-copyright">
					<p class="mono s-xxsmall">© <?= esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
				</div>

			</div>
		</div>
	</footer>


<?php wp_footer(); ?>

</body>
</html>
