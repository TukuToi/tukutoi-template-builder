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
 * @todo instead of the_content filter we probably should create a custom filter, which can be used instead of.
 *       The problem with the_content filter is that if we pass it multiple times, and someone hooks to it, it will get
 *       hooked many times. However correctly, the_content filter should be present only once on a post. We can not just
 *       omit it though, since without it, ShortCodes will not expand and styles won't apply.
 */

/**
 * Get Requested Template Options, ID and Settings.
 */
$tkt_options        = array_map( 'sanitize_key', get_option( 'tkt_available_templates', array() ) );
$template_id        = absint( apply_filters( 'tkt_template_id', 0 ) );
$template_settings  = array_map( 'sanitize_key', apply_filters( 'tkt_template_settings', array() ) );

/**
 * Check if a Global Header/Footer is available
 */
$global_header      = isset( $tkt_options['global_header'] ) && is_numeric( $tkt_options['global_header'] ) ? absint( $tkt_options['global_header'] ) : null;
$global_footer      = isset( $tkt_options['global_footer'] ) && is_numeric( $tkt_options['global_footer'] ) ? absint( $tkt_options['global_footer'] ) : null;

/**
 * Get our main template parts content
 */
$main_template_object = get_post( $template_id );

/**
 * Start our Layout with a header.
 */
if ( isset( $template_settings['header'] )
	&& 'theme_header' === $template_settings['header']
) {
	/**
	 * Header setting is set specifically to theme header.
	 */
	get_header();
} elseif ( isset( $template_settings['header'] ) && is_numeric( $template_settings['header'] ) ) {
	/**
	 * Header setting is set, and numeric => potentially a valid Custom Header is passed.
	 * If no valid header found, fall back to theme header.
	 */
	$header_object = get_post( $template_settings['header'] );
	if ( ! is_null( $header_object ) ) {
		$header_content = apply_filters( 'tkt_post_process_shortcodes', apply_filters( 'the_content', $header_object->post_content ) );
		// @codingStandardsIgnoreLine
		echo $header_content;
	} else {
		get_header();
	}
} elseif ( isset( $template_settings['header'] ) && 'no_header' === $template_settings['header'] ) {
	/**
	 * User chose to explicitly use this template but no header (perhaps they added header directly to template?).
	 */
	echo null;
} elseif ( ! is_null( $global_header ) ) {
	/**
	 * A global header is set, and no specific "local" header is set for this template.
	 * Again check if we find a valid header first.
	 */
	$header_object = get_post( $tkt_options['global_header'] );
	if ( ! is_null( $header_object ) ) {
		$header_content = apply_filters( 'tkt_post_process_shortcodes', $header_object->post_content );
		// @codingStandardsIgnoreLine
		echo apply_filters( 'the_content', $header_content );
	} else {
		get_header();
	}
} else {
	/**
	 * No theme_header, no custom header, no no_header, no global header, or else error.
	 * Fallback to Theme header.
	 */
	get_header();
}

/**
 * Output the Main Part.
 *
 * We already know the ID is set, so it is a valid template indeed.
 * Let's just for sanity check if the post exists.
 * If by some chance it does not, we return an error message.
 */
if ( ! is_null( $main_template_object ) ) {

	/**
	 * An existing main Template part is requested
	 */
	if ( isset( $template_settings['parent'] )
		&& ! empty( $template_settings['parent'] )
		&& is_numeric( $template_settings['parent'] )
	) {
		/**
		 * Parent setting is set, and numeric => potentially a valid Parent Template is passed.
		 */
		$parent_template_object = get_post( $template_settings['parent'] );
		if ( ! is_null( $parent_template_object ) ) {
			// @codingStandardsIgnoreLine
			echo apply_filters( 'the_content', $parent_template_object->post_content );
		}
	}

	$main_template = apply_filters( 'tkt_post_process_shortcodes', $main_template_object->post_content );
	$main_template = apply_filters( 'the_content', $main_template );
	// @codingStandardsIgnoreLine
	echo $main_template;

} else {
	$no_template_message = apply_filters( 'tkt_no_template_message', 'You have assigned a Template which does not exist. Perhaps it was deleted? ' );
	echo esc_html( $no_template_message );
}

/**
 * Output the footer
 */
if ( isset( $template_settings['footer'] )
	&& 'theme_footer' === $template_settings['footer']
) {
	/**
	 * Theme Footer is requested.
	 */
	get_footer();
} elseif ( isset( $template_settings['footer'] ) && is_numeric( $template_settings['footer'] ) ) {
	/**
	 * Footer setting is set, and numeric => potentially a valid Custom Footer is passed.
	 * If no valid footer found, fall back to theme footer.
	 */
	$footer_object = get_post( $template_settings['footer'] );
	if ( ! is_null( $footer_object ) ) {
		$footer_content = apply_filters( 'the_content', $footer_object->post_content );
		// @codingStandardsIgnoreLine
		echo $footer_content;
	} else {
		get_footer();
	}
} elseif ( isset( $template_settings['footer'] ) && 'no_footer' === $template_settings['footer'] ) {
	/**
	 * User chose to explicitly use this template but no footer (perhaps they added footer directly to template).
	 */
	echo null;
} elseif ( ! is_null( $global_footer ) ) {
	/**
	 * A global footer is set, and no specific "local" footer is set for this template.
	 * Again check if we find a valid footer first.
	 */
	$footer_object = get_post( $tkt_options['global_footer'] );
	if ( ! is_null( $footer_object ) ) {
		$footer_content = apply_filters( 'tkt_post_process_shortcodes', $footer_object->post_content );
		// @codingStandardsIgnoreLine
		echo apply_filters( 'the_content', $footer_content );
	} else {
		get_footer();
	}
} else {
	/**
	 * No theme_footer, no custom footer, no no_footer, no global footer, or else error.
	 * Fallback to Theme footer.
	 */
	get_footer();
}
