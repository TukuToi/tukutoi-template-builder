<?php
/**
 * The ShortCodes of the plugin.
 *
 * @link       https://www.tukutoi.com/
 * @since      1.3.0
 *
 * @package    Tkt_Template_Builder
 * @subpackage Tkt_Template_Builder/public
 */

/**
 * Defines all ShortCodes.
 *
 * @package    Tkt_Template_Builder
 * @subpackage Tkt_Template_Builder/public
 * @author     Your Name <hello@tukutoi.com>
 */
class Tkt_Template_Builder_Shortcodes {

	/**
	 * The unique prefix of this plugin.
	 *
	 * @since    1.3.0
	 * @access   private
	 * @var      string    $plugin_prefix    The string used to uniquely prefix technical functions of this plugin.
	 */
	private $plugin_prefix;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.3.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The Configuration object.
	 *
	 * @since    1.3.0
	 * @access   private
	 * @var      string    $declarations    All configurations and declarations of this plugin.
	 */
	private $declarations;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.3.0
	 * @param      string $plugin_prefix    The unique prefix of this plugin.
	 * @param      string $version          The version of this plugin.
	 * @param      object $declarations     The Configuration object.
	 * @param      object $sanitizer        The Sanitization object.
	 * @param      object $plugin_public    The Public object of this plugin.
	 */
	public function __construct( $plugin_prefix, $version, $declarations, $sanitizer, $plugin_public ) {

		$this->plugin_prefix    = $plugin_prefix;
		$this->version          = $version;
		$this->declarations     = $declarations;

		$this->sanitizer        = $sanitizer;
		$this->plugin_public    = $plugin_public;

	}

	/**
	 * TukuToi `[template]` ShortCode.
	 *
	 * Outputs a Template when used as ShortCode.</br>
	 *
	 * Example usage:
	 * `[template id="123"]`</br>
	 * For possible attributes see the Parameters > $atts section below or use the TukuToi ShortCodes GUI.
	 *
	 * @since    1.3.0
	 * @param array  $atts {
	 *      The ShortCode Attributes.
	 *
	 *      @type string    $id       The ID of the Template to load. Default: 0. Accepts: valid Template ID.
	 * }
	 * @param mixed  $content   ShortCode enclosed content. Not applicable for this ShortCode.
	 * @param string $tag       The Shortcode tag. Value: 'template'.
	 */
	public function template( $atts, $content = null, $tag ) {

		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			$tag
		);

		// Sanitize the User input atts.
		foreach ( $atts as $key => $value ) {
			$atts[ $key ] = $this->sanitizer->sanitize( 'absint', $value );
		}

		/**
		 * A Template is like passing $content to a ShortCode. They enclose other ShortCodes.
		 * Thus, we need to do_shortcode() and apply_filters.
		 *
		 * @since 1.3.0
		 * @todo Check if that is necessary and if we can check something like did_filter.
		 */
		$out = apply_filters( 'tkt_post_process_shortcodes', get_post( $atts['id'] )->post_content );
		$out = do_shortcode( $out );

		/**
		 * Return our output.
		 *
		 * @todo check how we could sanitize/escape this, if even needed.
		 * Technically this is the same as the_content, thus, no sanitization or escaping needed at this point.
		 */
		return $out;

	}

	/**
	 * TukuToi `[do_action]` ShortCode.
	 *
	 * Executes an Action.</br>
	 *
	 * Example usage:
	 * `[do_action hook_name="wp_head"]`</br>
	 * For possible attributes see the Parameters > $atts section below or use the TukuToi ShortCodes GUI.
	 *
	 * @since    2.0.0
	 * @param array  $atts {
	 *      The ShortCode Attributes.
	 *
	 *      @type string    $hook_name       The action to execute. Default: ''. Accepts: valid action tag.
	 *      @type string    ...$arg          Arguments to pass. Default: ''. Accepts: comma-delimited `argument:value` pairs.
	 * }
	 * @param mixed  $content   ShortCode enclosed content. Not applicable for this ShortCode.
	 * @param string $tag       The Shortcode tag. Value: 'do_action'.
	 */
	public function do_action( $atts, $content = null, $tag ) {

		$atts = shortcode_atts(
			array(
				'hook_name' => '',
				'args'      => '',
			),
			$atts,
			$tag
		);

		// Sanitize the User input atts.
		foreach ( $atts as $key => $value ) {
			if ( 'args' === $key ) {
				$value = $this->sanitizer->sanitize( 'text_field', $value );
				$args = array();
				if ( ! empty( $value ) ) {
					// If several args are passed.
					if ( strpos( $value, ',' ) !== false ) {
						$args_pre = explode( ',', $value );
						foreach ( $args_pre as $key => $arrval ) {
							list( $k, $v ) = explode( ':', $arrval );
							$args[ $k ] = is_numeric( $v ) ? (int) $v : '\'' . $v . '\'';
						}
					} else {
						list( $k, $v ) = explode( ':', $value );
						$args[ $k ] = is_numeric( $v ) ? (int) $v : '\'' . $v . '\'';
					}
				}
			} else {
				$atts[ $key ] = $this->sanitizer->sanitize( 'text_field', $value );
			}
		}

		/**
		 * Prepare the action args
		 * We support only plain strings ATM
		 *
		 * @since 1.25.0
		 */
		$action_args = null;
		if ( is_array( $args ) ) {
			$action_args = implode( ', ', $args );
		}

		/**
		 * If the Action echoes something or does something, which it should,
		 * we cannot output it directly, as that would under circumstances echo() a value inside a ShortCode.
		 * This is breaking ShortCodes. Thus, buffer the output.
		 */
		ob_start();
		if ( ! empty( $action_args ) ) {
			// Some arguments where passed.
			$out = do_action( $atts['hook_name'], $action_args );
		} else {
			// No arguments where passed.
			$out = do_action( $atts['hook_name'] );
		}
		$out = ob_get_clean();

		/**
		 * If the Output Buffer is empty, probably the action hooked is doing_it_wrong.
		 * However, we try to catch this and output directly, just in case the action returned something.
		 *
		 * @todo check what happens if an action does indeed echo, but echoes an empty string.
		 */
		if ( empty( $out ) ) {
			if ( ! empty( $action_args ) ) {
				// Some arguments where passed.
				$out = do_action( $atts['hook_name'], $action_args );
			} else {
				// No arguments where passed.
				$out = do_action( $atts['hook_name'] );
			}
		}

		/**
		 * Return our output.
		 *
		 * @todo check how we could sanitize/escape this, if even needed.
		 * Technically people would make sure of that in their add_action calls...
		 */
		return $out;

	}

	/**
	 * TukuToi `[funktion]` ShortCode.
	 *
	 * Executes a Function.</br>
	 * This ShortCode is named `funktion` not because we love German Language,
	 * but because PHP does not allow for a function with name `function` to be declared.
	 *
	 * Example usage:
	 * `[funktion function_name="my_function" args="color:red,size:big"]`</br>
	 * For possible attributes see the Parameters > $atts section below or use the TukuToi ShortCodes GUI.
	 *
	 * @since    2.0.0
	 * @param array  $atts {
	 *      The ShortCode Attributes.
	 *
	 *      @type string    $function_name   The Function to execute. Default: ''. Accepts: valid function tag.
	 *      @type string    ...$arg          Arguments to pass. Default: ''. Accepts: comma-delimited `argument:value` pairs.
	 * }
	 * @param mixed  $content   ShortCode enclosed content. Not applicable for this ShortCode.
	 * @param string $tag       The Shortcode tag. Value: 'funktion'.
	 */
	public function funktion( $atts, $content = null, $tag ) {

		$atts = shortcode_atts(
			array(
				'function_name' => '',
				'args'          => '',
			),
			$atts,
			$tag
		);

		// Sanitize the User input atts.
		foreach ( $atts as $key => $value ) {
			if ( 'args' === $key ) {
				$value = $this->sanitizer->sanitize( 'text_field', $value );
				$args = array();
				if ( ! empty( $value ) ) {
					// If several args are passed.
					if ( strpos( $value, ',' ) !== false ) {
						$args_pre = explode( ',', $value );
						foreach ( $args_pre as $key => $arrval ) {
							list( $k, $v ) = explode( ':', $arrval );
							$args[ $k ] = is_numeric( $v ) ? (int) $v : '\'' . $v . '\'';
						}
					} else {
						list( $k, $v ) = explode( ':', $value );
						$args[ $k ] = is_numeric( $v ) ? (int) $v : '\'' . $v . '\'';
					}
				}
			} else {
				$atts[ $key ] = $this->sanitizer->sanitize( 'text_field', $value );
			}
		}

		/**
		 * Prepare the function args
		 * We support only plain strings ATM
		 *
		 * @since 1.25.0
		 */
		$function_args = null;
		if ( is_array( $args ) ) {
			$function_args = implode( ', ', $args );
		}

		/**
		 * If the Function echoes something,
		 * we cannot output it directly, as that would under circumstances echo() a value inside a ShortCode.
		 * This is breaking ShortCodes. Thus, buffer the output.
		 */
		ob_start();
		if ( ! empty( $function_args ) ) {
			// Some arguments where passed.
			$out = call_user_func( $atts['function_name'], $function_args );
		} else {
			// No arguments where passed.
			$out = call_user_func( $atts['function_name'] );
		}
		$out = ob_get_clean();

		/**
		 * If the Output Buffer is empty, probably the function did not echo anything but returned something.
		 * We try to catch this and output directly, just in case function action returned something.
		 *
		 * @todo check what happens if a function does indeed echo, but echoes an empty string.
		 */
		if ( empty( $out ) ) {
			if ( ! empty( $function_args ) ) {
				$out = call_user_func( $atts['function_name'], $function_args );
			} else {
				$out = call_user_func( $atts['function_name'] );
			}
		}

		/**
		 * Return our output.
		 *
		 * @todo check how we could sanitize/escape this, if even needed.
		 * Technically people would make sure of that in their functions...
		 */
		return $out;

	}

}
