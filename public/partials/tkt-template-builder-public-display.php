<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://www.tukutoi/
 * @since      0.0.1
 *
 * @package    Tkt_Template_Builder
 * @subpackage Tkt_Template_Builder/public/partials
 */

$template_id = apply_filters( 'tkt_template_id', 0 );
$template_settings = apply_filters( 'tkt_template_settings', array() );

if ( isset( $template_settings['header'] ) && ! is_null( get_post( $template_settings['header'] ) ) ) {
	// load a minimal HEAD here
	// let the users filter this
	// Then load the rest of the user design (template).
	$tkt_header = get_post( $template_id );
} else {
	get_header();
}

if ( ! is_null( $template_id ) && ! is_null( get_post( $template_id ) ) ) {

	$tkt_main = do_shortcode( get_post( $template_id )->post_content );
	echo wp_kses_post( $tkt_main );

}

if ( isset( $template_settings['footer'] ) && ! is_null( get_post( $template_settings['footer'] ) ) ) {
	// Load user footer here.
	// Let the useres filter this.
	// Then load minimal footer elements needed.
	$tkt_footer = get_post( $template_id );
} else {
	get_footer();
}
