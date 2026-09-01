<?php
	/**
	 * Meta description.
	 *
	 * Prima c'era `get_the_excerpt()` scritto in linea nell'header. Tre difetti:
	 * l'estratto automatico di WordPress taglia a metà parola e ci appende
	 * `[…]` — sulla home usciva "…Get in touch at", troncata così; fuori dal
	 * loop `get_the_excerpt()` non è affidabile; e sugli archivi non c'era
	 * niente. Vedi [[SEO e contenuti]].
	 *
	 * Ordine: il campo scritto a mano vince sempre. Se manca si ricade su
	 * estratto o contenuto, ripuliti e tagliati **su una parola intera**, senza
	 * puntini di sospensione: una descrizione tronca dice al motore che nessuno
	 * l'ha scritta. Ultima istanza, la tagline del sito.
	 */

	// 155 caratteri: oltre, Google taglia da sé e il taglio è peggiore del nostro
	const STUDIOLUCENTI_META_DESC_MAX = 155;

	function studiolucenti_shorten_on_word( $text, $max ) {
		$text = trim( preg_replace( '/\s+/', ' ', $text ) );

		if ( function_exists( 'mb_strlen' ) ? mb_strlen( $text ) <= $max : strlen( $text ) <= $max ) {
			return $text;
		}

		$cut = function_exists( 'mb_substr' ) ? mb_substr( $text, 0, $max ) : substr( $text, 0, $max );
		$space = strrpos( $cut, ' ' );

		// nessuno spazio nei primi 155 caratteri: è una stringa senza parole,
		// meglio il taglio secco che restituire l'intero testo
		$cut = ( false === $space ) ? $cut : substr( $cut, 0, $space );

		// via la punteggiatura rimasta appesa dal taglio
		return rtrim( $cut, " ,;:–—-" );
	}

	function studiolucenti_meta_description() {
		$manual = '';

		if ( is_singular() && function_exists( 'get_field' ) ) {
			$manual = (string) get_field( 'meta_description', get_the_ID() );
		}

		if ( $manual ) {
			return studiolucenti_shorten_on_word( $manual, STUDIOLUCENTI_META_DESC_MAX );
		}

		$raw = '';

		if ( is_singular() ) {
			$post = get_post();
			// l'estratto scritto a mano se c'è, altrimenti il contenuto: mai
			// `get_the_excerpt()`, che è proprio quello che appende `[…]`
			$raw = $post ? ( $post->post_excerpt ?: $post->post_content ) : '';
		}

		if ( ! $raw ) {
			$raw = get_bloginfo( 'description' );
		}

		$raw = wp_strip_all_tags( strip_shortcodes( $raw ), true );

		return studiolucenti_shorten_on_word( $raw, STUDIOLUCENTI_META_DESC_MAX );
	}
