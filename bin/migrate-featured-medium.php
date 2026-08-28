<?php
/**
 * Migrazione di `featured_medium` da campo gallery a campo file.
 *
 * La gallery salvava un array serializzato di ID (`a:1:{i:0;s:2:"46";}`);
 * il campo file salva l'ID nudo (`46`). Senza questa conversione ACF legge
 * un array dove si aspetta un ID e la thumbnail sparisce.
 *
 * Va eseguito su OGNI ambiente: il database non è condiviso.
 *
 *   wp eval-file bin/migrate-featured-medium.php         # dry-run
 *   wp eval-file bin/migrate-featured-medium.php apply   # scrive
 *
 * Prima di scrivere: backup del database.
 */

global $wpdb;

// `--apply` non arriverebbe mai: WP-CLI intercetta tutto ciò che inizia con
// `--` come flag proprio e non lo passa allo script. L'argomento posizionale
// `apply` arriva in $args; le altre due forme sono ridondanze di sicurezza.
$apply = ( isset( $args ) && in_array( 'apply', (array) $args, true ) )
      || ( isset( $assoc_args ) && isset( $assoc_args['apply'] ) )
      || getenv( 'MIGRATE_APPLY' ) === '1';

$rows = $wpdb->get_results(
	"SELECT meta_id, post_id, meta_value
	   FROM {$wpdb->postmeta}
	  WHERE meta_key = 'featured_medium'"
);

$da_convertire = 0;
$gia_a_posto   = 0;
$anomali       = [];

foreach ( $rows as $row ) {
	// già un ID nudo: migrazione già passata, o riga scritta dal campo nuovo
	if ( is_numeric( $row->meta_value ) ) {
		$gia_a_posto++;
		continue;
	}

	$value = maybe_unserialize( $row->meta_value );

	// forma attesa: array con un solo ID
	if ( ! is_array( $value ) || count( $value ) !== 1 || ! is_numeric( reset( $value ) ) ) {
		$anomali[] = sprintf( 'post %d (meta_id %d): %s', $row->post_id, $row->meta_id, $row->meta_value );
		continue;
	}

	$id = (int) reset( $value );
	$da_convertire++;

	if ( $apply ) {
		$wpdb->update(
			$wpdb->postmeta,
			[ 'meta_value' => $id ],
			[ 'meta_id' => $row->meta_id ],
			[ '%d' ],
			[ '%d' ]
		);
	}
}

printf( "righe totali      : %d\n", count( $rows ) );
printf( "da convertire     : %d\n", $da_convertire );
printf( "già a posto       : %d\n", $gia_a_posto );
printf( "anomale (saltate) : %d\n", count( $anomali ) );

foreach ( $anomali as $a ) {
	printf( "  ! %s\n", $a );
}

echo $apply
	? "\nSCRITTO. Svuota la cache degli oggetti se ne usi una.\n"
	: "\nDRY-RUN: niente è stato modificato. Riesegui con `apply` per scrivere.\n";
