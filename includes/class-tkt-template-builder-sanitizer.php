<?php
/**
 * The file that contains the logic to sanitize, validate and handle errors.
 *
 * @link       https://www.tukutoi.com/
 * @since      1.0.0
 *
 * @package    Tkt_Template_Builder
 * @subpackage Tkt_Template_Builder/includes
 */

/**
 * The Sanitization, Validation and error handling Class.
 *
 * Registers all methods for sanitization, validation and error handling.
 *
 * @uses Tkt_Template_Builder_Declarations()
 * @since      1.0.0
 * @package    Tkt_Template_Builder
 * @subpackage Tkt_Template_Builder/includes
 * @author     Your Name <hello@tukutoi.com>
 */
class Tkt_Template_Builder_Sanitizer {

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
	 * Debug mode.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      bool    $debug    Wether Debug is enabled or not.
	 */
	private $debug;

	/**
	 * Debug log mode.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $debug_log    Debug logging mode.
	 */
	private $debug_log;

	/**
	 * The Configuration object.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @see class Tkt_Search_And_Filter_Declarations().
	 * @var      string    $declarations    All configurations and declarations of this plugin.
	 */
	private $declarations;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_prefix          The unique prefix of this plugin.
	 * @param      string $version          The version of this plugin.
	 * @param      array  $declarations    The Configuration object.
	 */
	public function __construct( $plugin_prefix, $version, $declarations ) {

		$this->plugin_prefix    = $plugin_prefix;
		$this->version          = $version;
		$this->declarations     = $declarations;

		$this->debug            = false;
		$this->debug_log        = false;

	}

	/**
	 * Route the value through the correct sanitization callback.
	 * Return the sanitized value or empty if all fails.
	 *
	 * @since 1.0.0
	 * @param string $type The type of sanitization to apply.
	 * @param array  $args The arguments to pass to the user function.
	 * @return mixed $value sanitized value.
	 */
	private function handle_sanitizer( $type = '', $args = array() ) {

		$value = '';

		if ( ! empty( $type )
			&& array_key_exists( $type, $this->declarations->sanitization_options )
			&& 'none' !== $type
			&& ! empty( $args )
		) {

			// Sanitization should happen, and sanitization callback exist.
			$value = call_user_func_array( $this->declarations->sanitization_options[ $type ]['callback'], $args );

		} elseif ( ! empty( $type )
			&& 'none' === $type
			&& ! empty( $args )
		) {

			// Sanitization is explicitly set to none, thus should not happen.
			$value = array_shift( $args );

		} elseif ( empty( $type ) ||
			( ! empty( $type )
				&& ! array_key_exists( $type, $this->declarations->sanitization_options )
			)
			&& ! empty( $args )
		) {

			// Either the sanitization argument is missing, invalid or malformed.
			// Be safe and escape as it could be user error or else.
			$value = call_user_func_array( $this->declarations->sanitization_options['text_field']['callback'], $args );

		} else {

			// Something went VERY wrong. Be safe.
			$value = '';

		}

		return $value;

	}

	/**
	 * Validate Object.
	 *
	 * @since 1.0.0
	 * @param mixed $value The value to validate.
	 * @return object $value | wp_error validated object (NOT Sanitized) or wp_error on failure.
	 */
	private function handle_object_validation( $value ) {

		if ( ! is_object( $value )
			|| is_null( $value )
		) {

			return new WP_Error( 'no_object', esc_html__( 'This is not an object', 'tkt-template-builder' ) );

		} elseif ( is_object( $value ) ) {

			return $value;

		} else {

			return new WP_Error( 'no_object', esc_html__( 'Something else went wrong', 'tkt-template-builder' ) );

		}

	}

	/**
	 * Validate Object, then validate requested property.
	 *
	 * @since 1.0.0
	 * @param mixed  $value The value to validate.
	 * @param string $prop The property to validate and return.
	 * @return mixed $value->$prop | wp_error validated property value (NOT Sanitized) or wp_error on failure.
	 */
	private function handle_object_prop_validation( $value, $prop ) {

		if ( ! is_wp_error( $this->handle_object_validation( $value ) )
			&& ! property_exists( $value, $prop )
		) {

			// Translators: "%s" stands for an object property. "%s" should not be translated.
			return new WP_Error( 'no_property', sprintf( esc_html__( 'The property "%s" does not exist in the Object', 'tkt-template-builder' ), $prop ) );

		} elseif ( ! is_wp_error( $this->handle_object_validation( $value ) )
			&& property_exists( $value, $prop )
		) {

			return $value->$prop;

		} else {

			return new WP_Error( 'no_property', esc_html__( 'Something else went wrong', 'tkt-template-builder' ) );

		}

	}

	/**
	 * Validate Array.
	 *
	 * @since 1.0.0
	 * @param mixed $value The value to validate.
	 * @return array $value | wp_error validated array (NOT Sanitized) or wp_error on failure.
	 */
	private function handle_array_validation( $value ) {

		if ( is_array( $value ) ) {

			return $value;

		} else {

			return new WP_Error( 'no_array', esc_html__( 'This is not an array', 'tkt-template-builder' ) );

		}

	}

	/**
	 * Validate Operators.
	 *
	 * @since 1.0.0
	 * @param mixed $value The value to validate.
	 * @return string $value | wp_error validated opereator value (NOT Sanitized) or wp_error on failure.
	 */
	private function handle_operator_validation( $value ) {

		$valid_operators = array_flip( $this->declarations->data_map( 'valid_operators' ) );

		if ( in_array( $value, $valid_operators ) ) {

			return $value;

		} else {

			return new WP_Error( 'invalid_opeartor', esc_html__( 'This is not a valid Operator', 'tkt-template-builder' ) );

		}

	}

	/**
	 * Public facing Sanitization Callaback.
	 *
	 * @since 1.0.0
	 * @param string $type The type of sanitization to apply.
	 * @param mixed  $value The value to sanitize.
	 * @param string $item  The meta key of the field to sanitize.
	 * @param string $object_type The object type of the meta to sanitize.
	 * @param string $object_subtype The object subtype of the meta to sanitize.
	 * @return mixed $value sanitized value.
	 */
	public function sanitize( $type, $value, $item = '', $object_type = '', $object_subtype = '' ) {

		// Get all arguments of this function with their values.
		$args = func_get_args();

		// Remove the first argument $type, since it is not a argument of the sanitization callback.
		$real_args = array_slice( $args, 1 );

		// Get sanitized return value.
		$value = $this->handle_sanitizer( $type, $real_args );

		return $value;

	}

	/**
	 * Public facing Validation Callback.
	 *
	 * @since 1.0.0
	 * @param string $type The type of validation to apply.
	 * @param mixed  $value The value to validate.
	 * @param string $prop  The property or array key to validate.
	 * @return mixed $value validated value.
	 */
	public function validate( $type, $value, $prop = null ) {

		if ( 'object' === $type &&
			! empty( $value )
			&& is_null( $prop )
		) {

			// An object validation is requested.
			$value = $this->handle_object_validation( $value );

		} elseif ( 'object' === $type
			&& ! empty( $value )
			&& ! is_null( $prop )
		) {

			// An object validation is requested with property validation.
			$value = $this->handle_object_prop_validation( $value, $prop );

		} elseif ( 'array' === $type
			&& ! empty( $value )
			&& is_null( $prop )
		) {

			// An array validation is requested.
			$value = $this->handle_array_validation( $value );

		} elseif ( 'operation' == $type ) {

			// An operation validation is requested.
			$value = $this->handle_operator_validation( $value );

		} else {

			return;// We could add more validation calls here, like for array members, etc.

		}

		return $value;

	}

	/**
	 * Check for WP Errors, null or false.
	 *
	 * @since 1.0.0
	 * @param mixed $result The value to check.
	 * @return bool true if has errors, false if not.
	 */
	public function invalid_or_error( $result ) {

		if ( is_null( $result )
			|| is_wp_error( $result )
			|| false === $result
		) {
			return true;
		}

		return false;

	}

	/**
	 * Return Errors to debug log if active.
	 *
	 * @since 1.0.0
	 * @param mixed  $result The value to get error of.
	 * @param string $location The method where the error happened.
	 * @param string $backtrace The Debug backtrace to the error.
	 * @return array $errors Array of  (NOT Sanitized).
	 */
	public function get_errors( $result, $location, $backtrace ) {

		$errors = array();

		$errors['display'] = esc_html__( 'Something wrong. Enable Debug mode and check again.', 'tkt-template-builder' );

		if ( is_null( $result ) ) {
			$errors['debug']  = 'The response was null in ' . $location;
		} elseif ( is_wp_error( $result ) ) {
			$errors['debug']  = 'The response was an instance of wp_error: ' . $result->get_error_message() . ' in ' . $location;
		} elseif ( false === $result ) {
			$errors['debug']  = 'There was a failure in response in ' . $location;
		} else {
			$errors['debug'] = 'Unknown type of error occurred in' . $location;
		}

		if ( true === $this->debug ) {
			/**
			 * Reviewers:
			 * All debug logs are expected here.
			 * They are only active if debug is enabled in this class, which by default is false.
			 */
			if ( true === $this->log_debug ) {
				error_log( $errors['debug'] . ' This is the full backlog: ' . print_r( $backtrace, true ) );// @codingStandardsIgnoreLine
			}
			return $errors['debug'];
		}
		if ( true === $this->log_debug ) {
			error_log( $errors['debug'] . ' This is the full backlog: ' . print_r( $backtrace, true ) );// @codingStandardsIgnoreLine
		}

		return $errors['display'];

	}

}
