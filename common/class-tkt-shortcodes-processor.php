<?php
/**
 * The file that defines the ShortCode processor.
 *
 * Defines logic to resolve inner ShortCodes and allow custom shortcodes
 * to be used in HTML attributes, or ShortCode attributes.
 *
 * [Toolset Views](https://toolset.com/) by [OTGS](https://onthegosystems.com/)
 * was used as a loose inspiration for some of the code in this class.
 *
 * @link       https://www.tukutoi.com/
 * @since      1.0.0
 *
 * @package    Tkt_Common
 */

/**
 * The ShortCode Resolver Class.
 *
 * This is used to make shortcodes nested in ShortCode attributes,
 * or HTML attributes work.
 * WordPress 4.2.3 (thus also CP) breaks all ShortCodes in ShortCode attributes,
 * or ShortCodes in HTML attributes.
 *
 * @see https://wptavern.com/plugin-developers-demand-a-better-security-release-process-after-wordpress-4-2-3-breaks-thousands-of-websites
 *
 * This class attempts to fix that.
 *
 * @since      1.3.0
 * @package    Tkt_Common
 * @author     Your Name <hello@tukutoi.com>
 */
class Tkt_Shortcodes_Processor {

	/**
	 * The unique prefix of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_prefix    The string used to uniquely prefix technical functions of this plugin.
	 */
	private $plugin_prefix;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The Configuration object.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $declarations    All configurations and declarations of this plugin.
	 */
	private $declarations;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.3.0
	 * @param    string $plugin_prefix    The unique prefix of this plugin.
	 * @param    string $version          The version of this plugin.
	 * @param    array  $declarations    The Configuration object.
	 */
	public function __construct( $plugin_prefix, $version, $declarations ) {

		$this->plugin_prefix    = $plugin_prefix;
		$this->version          = $version;
		$this->declarations     = $declarations;
		$this->shortcode_start  = '{¡{';
		$this->shortcode_end    = '}¡}';
		$this->loop_shortcode   = 'tkt_scs_loop';
		$this->base64_prefix    = 'tkt_base64_';

	}

	/**
	 * Public facing filter callback on the_content.
	 *
	 * @since    1.3.0
	 * @param    string $content  The Post Content.
	 */
	public function pre_process_shortcodes( $content ) {

		// No need to proceed if empty.
		if ( empty( $content ) ) {
			return $content;
		}

		remove_filter( 'the_content', 'wpautop', 10 );
		remove_filter( 'the_excerpt', 'wpautop', 10 );

		$content = $this->apply_encoder( $content );

		return $content;

	}

	/**
	 * Public facing filter callback on the_content.
	 *
	 * @since    1.3.0
	 * @param    string $content  The Post Content.
	 */
	public function post_process_shortcodes( $content ) {

		// No need to proceed if empty.
		if ( empty( $content ) ) {
			return $content;
		}

		$content = $this->apply_decoder( $content );

		return $content;

	}

	/**
	 * Encoders and Resolvers passed to the pre_process_shortcodes callback.
	 *
	 * @since    1.3.0
	 * @param    string $content  The Post Content.
	 * @return   string $content The Post Content encoded and with first level of inner ShortCodes resolved.
	 */
	private function apply_encoder( $content ) {

		// Encode ShortCodes inside HTML attributes to use {¡{shortcode}¡} format.
		$content = $this->encode_html_attribute_shortcodes( $content );
		// Encode repeating content (loop) to base 64.
		$content = $this->encode_iterators( $content );
		// Resolve all remaining inner ShortCodes ([shortcode attr="[inner_shortcode]"]).
		$content = $this->resolve_inner_shortcodes( $content );

		return $content;

	}

	/**
	 * Decoders and Resolvers passed to the post_process_shortcodes callback.
	 *
	 * @since    1.3.0
	 * @param    string $content  The Post Content.
	 * @return   string $content The Post Content Decoded and Resolved.
	 */
	private function apply_decoder( $content ) {

		// Decode base64 ShortCodes.
		$content = $this->decode_iterators( $content );
		// Resolve inner ShortCodes.
		$content = $this->resolve_inner_shortcodes( $content );
		// Decode ShortCodes inside HTML attributes back from {¡{shortcode}¡} format and resolve.
		$content = $this->resolve_html_shortcodes( $content );

		return $content;

	}

	/**
	 * WordPress does not like ShortCodes inside HTML attributes.
	 *
	 * Encode all [shortcodes] (the applicable ones) to use {¡{shortcode}¡} format.
	 *
	 * @since 2.17.0
	 * @param    string $content  The Post Content.
	 * @return string $content The Post Content with encoded HTML attribute ShortCodes.
	 */
	private function encode_html_attribute_shortcodes( $content ) {
		// Normalize entities.
		$trans = array(
			'&#91;' => '&#091;', // Encoded [.
			'&#93;' => '&#093;', // Encoded ].
		);
		$content = strtr( $content, $trans );

		$textarr = $this->html_split( $content );
		$inner_expressions = $this->get_inner_expressions();
		if ( ! empty( $inner_expressions && isset( $inner_expressions['regex'] ) ) ) {
			foreach ( $textarr as &$element ) {

				if ( $this->should_skip_html_node( $element ) ) {
					continue;
				}

				// Look for every valid shortcode inside the node, and expand it.
				foreach ( $inner_expressions as $shortcode ) {

					$counts = preg_match_all( $shortcode, $element, $matches );

					if ( $counts > 0 ) {
						foreach ( $matches[0] as $index => &$match ) {

							$string_to_replace = $match;

							$replacement = str_replace( '[', $this->shortcode_start, $match );
							$replacement = str_replace( ']', $this->shortcode_end, $replacement );
							$element = str_replace( $string_to_replace, $replacement, $element );

						}
					}
				}
			}
		}

		$content = implode( '', $textarr );

		return $content;
	}

	/**
	 * Encode loops to base64.
	 *
	 * Encode all `loop` ShortCodes contents to base64 so WordPress does
	 * not attempt to resolve them until we explicitly want to.
	 *
	 * @since 2.17.0
	 * @param    string $content  The Post Content.
	 * @return string $content The Post Content with encoded loops.
	 */
	private function encode_iterators( $content ) {

		if ( false === strpos( $content, '[' . $this->loop_shortcode ) ) {
			return $content;
		}

		// Get only properly configured loops (enclosing).
		$expression = '/\\[' . $this->loop_shortcode . '.*?\\](.*?)\\[\\/' . $this->loop_shortcode . '\\]/is';
		$counts = preg_match_all( $expression, $content, $matches );

		foreach ( $matches[0] as $index => $match ) {
			// Encode the data to stop WP from trying to fix or parse it.
			// The iterator shortcode will manage this on render.
			$match_encoded = str_replace( $matches[1][ $index ], $this->base64_prefix . base64_encode( $matches[1][ $index ] ), $match );
			$content = str_replace( $match, $match_encoded, $content );
		}

		return $content;
	}

	/**
	 * Resolve internal shortcodes.
	 *
	 * @since 1.3.0
	 * @param string $content The Post Content.
	 * @return string
	 */
	private function resolve_inner_shortcodes( $content ) {

		// Search for outer shortcodes, to process their inner expressions.
		$outer_shortcodes = array();
		$counts = $this->find_outer_brackets( $content, $outer_shortcodes );

		// Iterate shortcode elements and resolve their internal shortcodes, one by one.
		if ( 0 < $counts ) {

			$inner_expressions = $this->get_inner_expressions();
			if ( ! empty( $inner_expressions && isset( $inner_expressions['regex'] ) ) ) {
				foreach ( $outer_shortcodes as $outer_shortcode ) {

					foreach ( $inner_expressions as $inner_expression ) {

						$inner_counts = preg_match_all( $inner_expression, $outer_shortcode, $inner_shortcodes );

						// Replace all inner shortcodes.
						if ( 0 < $inner_counts ) {

							foreach ( $inner_shortcodes[0] as &$inner_shortcode ) {

								$replacement = do_shortcode( $inner_shortcode );
								$content = str_replace( $inner_shortcode, $replacement, $content );
							}
						}
					}
				}
			}
		}

		return $content;
	}

	/**
	 * Decode base64.
	 *
	 * If the Content has `loop` shortcodes, these are encoded to base64.
	 * At this point we resolved surrouding shortcodes, and need to prepare the loops for resolution.
	 *
	 * @since 2.17.0
	 * @param    string $content  The Post Content with base64 encoded loops.
	 * @return   string $content  The Post Content with decoded (but not resolved) loops.
	 */
	private function decode_iterators( $content ) {

		if ( 0 === strpos( $content, $this->base64_prefix ) ) {

			$content = substr( $content, strlen( $this->base64_prefix ) );
			$content = base64_decode( $content );

		}

		return $content;

	}

	/**
	 * Decode and resolve ShortCodes in HTML attributes.
	 *
	 * If there are encoded {¡{shortcodes}¡} then it means they are used in HTML attributes.
	 * Revert them to the native [shortcode] syntax, and resolve them.
	 *
	 * @since 2.17.0
	 * @param    string $content  The Post Content with encoded HTML attribute ShortCodes.
	 * @return   string $content  The Post Content with decoded and resolved HTML attribute ShortCodes.
	 */
	private function resolve_html_shortcodes( $content ) {

		$shortcodes = $this->get_all_matches_between( $content, $this->shortcode_start, $this->shortcode_end );

		foreach ( $shortcodes as $key => $shortcode ) {

			$executed = do_shortcode( '[' . $shortcode . ']' );
			$content = str_replace( $this->shortcode_start . $shortcode . $this->shortcode_end, $executed, $content );

		}

		return $content;

	}

	/**
	 * Separate HTML elements and comments from the text.
	 *
	 * Heavily inspired in wp_html_split.
	 *
	 * @param string $input The text which has to be formatted.
	 * @return array The formatted text.
	 * @since 3.3.0
	 */
	private function html_split( $input ) {
		$comments =
				'!'         // Start of comment, after the <.
			. '(?:'         // Unroll the loop: Consume everything until --> is found.
				. '-(?!->)' // Dash not followed by end of comment.
				. '[^\-]*+' // Consume non-dashes.
			. ')*+'         // Loop possessively.
			. '(?:-->)?';   // End of comment. If not found, match all input.

		$cdata =
				'!\[CDATA\['// Start of comment, after the <.
			. '[^\]]*+'     // Consume non-].
			. '(?:'         // Unroll the loop: Consume everything until ]]> is found.
				. '](?!]>)' // One ] not followed by end of comment.
				. '[^\]]*+' // Consume non-].
			. ')*+'         // Loop possessively.
			. '(?:]]>)?';   // End of comment. If not found, match all input.

		$regex =
				'/('                     // Capture the entire match.
				. '<'                    // Find start of element.
				. '(?(?=!--)'            // Is this a comment?
					. $comments          // Find end of comment.
				. '|'
					. '(?(?=!\[CDATA\[)' // Is this a comment?
						. $cdata         // Find end of comment.
					. '|'
						. '[^>]*>?'      // Find end of element. If not found, match all input.
					. ')'
				. ')'
			. ')/s';

		return preg_split( $regex, $input, -1, PREG_SPLIT_DELIM_CAPTURE );
	}

	/**
	 * Get ShortCodes we accept as inner ShortCodes, or HTML attributes.
	 *
	 * Only registered, non-enclosing ShortCodes are allowed.
	 * Users can add theyr Custom ShortCodes by using the filter tkt_scs_custom_inner_shortcodes
	 *
	 * @since 1.3.0
	 * @return array $inner_expressions An array of registered shortcodes.
	 */
	private function get_inner_expressions() {

		$inner_expressions = array();

		$native_shortcodes = $this->get_native_inner_shortcodes();
		if ( ! empty( $native_shortcodes ) ) {
			$inner_expressions['regex'] = '/\\[(' . implode( '|', $native_shortcodes ) . ').*?\\]/i';
		}

		$custom_shortcodes = $this->get_custom_shortcodes();
		if ( count( $custom_shortcodes ) > 0 ) {
			$inner_expressions['regex'] = '/\\[(' . implode( '|', $custom_shortcodes ) . '|' . implode( '|', $native_shortcodes ) . ').*?\\]/i';
		}

		return $inner_expressions;
	}

	/**
	 * Check if HTML note should be skipped.
	 *
	 * It is not necessary to process all this in HTML comments, or CDATA items.
	 *
	 * @since 2.170
	 * @param mixed $element The HTML node to check.
	 * @return bool false|true Whether the Node should be skipped.
	 */
	private function should_skip_html_node( $element ) {
		if (
			'' === $element
			|| '<' !== $element[0]
		) {
			// This element is not an HTML tag.
			return true;
		}

		$noopen = false === strpos( $element, '[' );
		$noclose = false === strpos( $element, ']' );
		if (
			$noopen
			|| $noclose
		) {
			// This element does not contain shortcodes.
			return true;
		}

		if (
			'<!--' === substr( $element, 0, 4 )
			|| '<![CDATA[' === substr( $element, 0, 9 )
		) {
			// This element is a comment or a CDATA piece.
			return true;
		}

		return false;
	}

	/**
	 * Find shortcodes that contain other shortcodes as attribute values,
	 * and populate a list of their opening tag, to process those internal shortcodes.
	 *
	 * @since 1.3.0
	 * @param string $content The content to check.
	 * @param array  $matches List of shortcodes: full shortcode without brackets.
	 * @return int $count Number of top level shortcodes found.
	 */
	private function find_outer_brackets( $content, &$matches ) {
		$count = 0;

		$first = strpos( $content, '[' );
		if ( false !== $first ) {
			$length = strlen( $content );
			$brace_count = 0;
			$brace_start = -1;
			for ( $i = $first; $i < $length; $i++ ) {
				if ( '[' === $content[ $i ] ) {
					if ( 0 === $brace_count ) {
						$brace_start = $i + 1;
					}
					$brace_count++;
				}
				if ( ']' === $content[ $i ] ) {
					if ( $brace_count > 0 ) {
						$brace_count--;
						if ( 0 === $brace_count ) {
							$inner_content = substr( $content, $brace_start, $i - $brace_start );
							if (
								! empty( $inner_content )
								&& $this->has_shortcode_as_attribute_value( $inner_content )
								&& $this->is_unbracketed_shortcode( $inner_content )
							) {
								$matches[] = $inner_content;
								$count++;
							}
						}
					}
				}
			}
		}

		return $count;
	}

	/**
	 * Make sure that a content which presumes to be a shortcode without brackets
	 * does contain an inner shortcode.
	 *
	 * @since 1.3.0
	 * @param string $unbracketed_shortcode The content to check.
	 * @return bool Whether the Inner ShortCode is present.
	 */
	private function has_shortcode_as_attribute_value( $unbracketed_shortcode ) {
		$has_inner_shortcode = strpos( $unbracketed_shortcode, '[' );

		return ( false !== $has_inner_shortcode );
	}

	/**
	 * Make sure that a given content is indeed a shortcode without brackets:
	 * - Can not start with a closing tag delimiter.
	 * - Can not start with a bracket for another inner shortcode.
	 * - Must start with a valid shortcode tag.
	 *
	 * @since 1.3.0
	 * @param string $unbracketed_shortcode The content to check.
	 * @return bool Whether the ShortCode is without brackets.
	 */
	private function is_unbracketed_shortcode( $unbracketed_shortcode ) {
		// Is this a closing shortcode perhaps?
		if ( 0 === strpos( $unbracketed_shortcode, '/' ) ) {
			return false;
		}

		// Is this a doube brackets structure perhaps?
		if ( 0 === strpos( $unbracketed_shortcode, '[' ) ) {
			return false;
		}

		$shortcode_pieces = explode( ' ', $unbracketed_shortcode );

		// Does this start with a valid shortcode tag?
		return (
			isset( $shortcode_pieces[0] )
			&& ! empty( $shortcode_pieces[0] )
			&& shortcode_exists( $shortcode_pieces[0] )
		);
	}

	/**
	 * Get all matches of between 2 delimiters.
	 *
	 * This generic helper function finds any string starting and ending with a flag.
	 * It then returns an array where the values are the strings between each start and end flag.
	 *
	 * @since 2.17.0
	 * @param    string $str   The string to check.
	 * @param    string $start The start flag to check for.
	 * @param    string $end   The end flag to check for.
	 * @return   array $contents  The found occurrences of string betwen start and end.
	 */
	public function get_all_matches_between( $str, $start, $end ) {

		$contents = array();
		$start_l = strlen( $start );
		$end_l = strlen( $end );

		$start_match = $match_start = $match_end = 0;
		while ( false !== ( $match_start = strpos( $str, $start, $start_match ) ) ) {
			$match_start += $start_l;
			$match_end = strpos( $str, $end, $match_start );
			if ( false === $match_end ) {
				break;
			}
			$contents[] = substr( $str, $match_start, $match_end - $match_start );
			$start_match = $match_end + $end_l;
		}

		return $contents;
	}

	/**
	 * Get a list of inbuilt ShortCodes allowed inside other Shortcodes or HTML.
	 *
	 * @since 1.3.0
	 * @return array
	 */
	public function get_native_inner_shortcodes() {

		$native_shortcodes = array();

		foreach ( $this->declarations->shortcodes as $shortcode => $array ) {

			if ( true === $array['inner'] ) {
				$native_shortcodes[] = 'tkt_scs_' . $shortcode;
			}
		}

		return $native_shortcodes;

	}

	/**
	 * Get the list of registered shortcodes to be used inside other shortcodes.
	 *
	 * @since 1.3.0
	 * @return array
	 */
	public function get_custom_shortcodes() {
		$custom_shortcodes = array();

		/**
		 * Filter the list of custom shortcodes that can be used inside other shortcodes or as HTML attribute values.
		 *
		 * @param array $custom_shortcodes List of shortcodes.
		 * @return array
		 * @since 1.3.0
		 */
		$custom_shortcodes = apply_filters( 'tkt_scs_custom_inner_shortcodes', $custom_shortcodes );

		return array_unique( $custom_shortcodes );
	}

}
