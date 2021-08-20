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

/**
 * The warnings to escape the output here are to be ignored.
 * Not even WordPress uses wp_kses (or (any other escape/sanitize function at all) ) when it outputs its content or titles.
 * The trick here is that this content we output can only be saved by trusted, update_core cap users.
 * If we where to escape this content, we would need a wp_kses that allows for _all_ possible HTML you can think of.
 * And we _cannot_ guess all possible HTML, for example, `<div my-custom-attribute="value">` is perfectly valid HTML.
 * However, you could _never_ guess that in a wp_kses. This is the reason WP Core itself does not escape the_content as well.
 *
 * Follow the trail:
 * A) A theme outputs the content with the function the_content(). It will be unescaped, of course.
 * B) the_content() echoes $content and the only filter applied to that is the_content filter. There is no escaping.
 * C) the_content() gets its content from get_the_content, which again outputs it completely unescaped/unsanitized.
 * D) get_the_content() in turn gets its content directly from the $pages global, which is raw Database Content. NOT sanitized.
 *
 * POC:
 * - Put a `<script>alert('got you');</script>` in your text editor as admin
 * - Save,
 * - Visit the post as a guest.
 *
 * If the content were sanitized or escaped at any point, then you would not see the alert.
 *
 * In case you still think we should escape this, take it up with WordPress itself.
 * - Any TukuToi template is only editable by users who have udpate_core cap.
 * - Any TukuToi Shortcode is safely sanitized and escaped where needed.
 * - Any HTML a user with update_core caps inputs in a editor (and that includes JS inside script tags) is the users responsibility.
 * - If a user with cap "update_core" cannot decide what is safe and what not, then they should not manage a WP Site.
 *
 * This is why in this file we use @codingStandardsIgnoreLine.
 * Think about what this means: factually, EVERY WordPress website is presenting 100% unescaped content.
 *
 * @see https://make.wordpress.org/core/handbook/testing/reporting-security-vulnerabilities/#why-are-some-users-allowed-to-post-unfiltered-html
 * @see {/wp-includes/post-template.php}
 * @see https://www.tollmanz.com/wp-kses-performance/
 */

$template_id = apply_filters( 'tkt_template_id', 0 );
$template_settings = apply_filters( 'tkt_template_settings', array() );

if ( isset( $template_settings['header'] )
	&& ( 'theme_header' === $template_settings['header']
		|| empty( $template_settings['header'] )
	)
	|| ! isset( $template_settings['header'] )
) {
	/**
	 * Header setting is set, and is either set to theme_header or empty => load theme header.
	 * Header setting is not set at all => load theme header.
	 */
	get_header();
} elseif ( isset( $template_settings['header'] ) && is_numeric( $template_settings['header'] ) ) {
	/**
	 * Header setting is set, and numeric => potentially a valid Custom Header is passed.
	 * If no valid header found, fall back to theme header.
	 */
	if ( ! is_null( get_post( $template_settings['header'] ) ) ) {
		// @codingStandardsIgnoreLine
		echo apply_filters( 'the_content', get_post( $template_settings['header'] )->post_content );
	} else {
		get_header();
	}
} elseif ( isset( $template_settings['header'] ) && 'no_header' === $template_settings['header'] ) {
	/**
	 * User chose to explicitly use this template but no header (perhaps they added header directly to template).
	 */
	echo null;
}

/**
 * Output the Main Template
 *
 * We already know the ID is set, so it is a valid template indeed.
 * Let's just for sanity check if the post exists.
 * If by some chance it does not, we return an error message.
 *
 * NOTE: The WPCS alarm on line 96 is again false. We DO escape our translated and echoed content.
 */
if ( ! is_null( $template_id ) && ! is_null( get_post( $template_id ) ) ) {
	// @codingStandardsIgnoreLine
	echo apply_filters( 'the_content', get_post( $template_id )->post_content );

} else {
	$message = apply_filters( 'tkt_no_layout_message', 'You have assigned a Layout to this Template or Template Part which does not exist. Perhaps it was deleted? ' );
	// Translators: 1: Error message. @codingStandardsIgnoreLine
	printf( esc_html__( '%s', 'tkt-template-builder' ), $message );
}

if ( isset( $template_settings['footer'] )
	&& ( 'theme_footer' === $template_settings['footer']
		|| empty( $template_settings['footer'] )
	)
	|| ! isset( $template_settings['footer'] )
) {
	/**
	 * Footer setting is set, and is either set to theme_header or empty => load theme footer.
	 * Footer setting is not set at all => load theme footer.
	 */
	get_footer();
} elseif ( isset( $template_settings['footer'] ) && is_numeric( $template_settings['footer'] ) ) {
	/**
	 * Footer setting is set, and numeric => potentially a valid Custom Footer is passed.
	 * If no valid footer found, fall back to theme footer.
	 */
	if ( ! is_null( get_post( $template_settings['footer'] ) ) ) {
		// @codingStandardsIgnoreLine
		echo apply_filters( 'the_content', get_post( $template_settings['footer'] )->post_content );
	} else {
		get_footer();
	}
} elseif ( isset( $template_settings['footer'] ) && 'no_footer' === $template_settings['footer'] ) {
	/**
	 * User chose to explicitly use this template but no footer (perhaps they added footer directly to template).
	 */
	echo null;
}
