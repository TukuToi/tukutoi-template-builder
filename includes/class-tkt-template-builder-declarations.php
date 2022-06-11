<?php
/**
 * The Declarations File of this Plugin.
 *
 * Registers an array of ShortCodes with localised labels,
 * as well maintains a list of arrays containing object properties and array members
 * which are used allover this plugin, and a list of all sanitization options, plus their callbacks.
 *
 * @link       https://www.tukutoi.com/
 * @since      1.0.0
 *
 * @package    Tkt_Template_Builder
 * @subpackage Tkt_Template_Builder/includes
 */

/**
 * The Declarations Class.
 *
 * This is used both in public and admin when we need an instance of all shortcodes,
 * or a centrally managed list of object properties or array members where we cannot already
 * get it from the code (such as user object, which is a entangled mess, or get_bloginfo which is a case switcher).
 *
 * @since      1.0.0
 * @package    Tkt_Template_Builder
 * @subpackage Tkt_Template_Builder/includes
 * @author     TukuToi <hello@tukutoi.com>
 */
class Tkt_Template_Builder_Declarations {

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
	 * The ShortCodes of this plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array    $shortcodes    All ShortCode tags, methods and labels of this plugin.
	 */
	public $shortcodes;

	/**
	 * The Sanitization options and callbacks.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array    $sanitization_options    All Sanitization Options of this plugin and their callbacks.
	 */
	public $sanitization_options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->shortcodes       = $this->declare_shortcodes();
		$this->sanitization_options = $this->sanitize_options();

	}

	/**
	 * Register an array of Shortcodes of this plugin
	 *
	 * Multidimensional array keyed by ShortCode tagname,
	 * each holding an array of ShortCode data:
	 * - Label
	 * - Type
	 *
	 * @since 1.0.0
	 * @return array $shortcodes The ShortCodes array.
	 */
	private function declare_shortcodes() {

		$shortcodes = array(
			'template' => array(
				'label' => esc_html__( 'Template', 'tkt-template-builder' ),
				'type'  => 'templating',
				'inner' => false,
			),
			'navmenu' => array(
				'label' => esc_html__( 'Navigation Menu', 'tkt-template-builder' ),
				'type'  => 'templating',
				'inner' => false,
			),
			'widget' => array(
				'label' => esc_html__( 'Widget', 'tkt-template-builder' ),
				'type'  => 'templating',
				'inner' => false,
			),
			'sidebar' => array(
				'label' => esc_html__( 'Sidebar', 'tkt-template-builder' ),
				'type'  => 'templating',
				'inner' => false,
			),
			'do_action' => array(
				'label' => esc_html__( 'Do Action', 'tkt-template-builder' ),
				'type'  => 'hooks',
				'inner' => true,
			),
			'add_filter' => array(
				'label' => esc_html__( 'Add Filter', 'tkt-template-builder' ),
				'type'  => 'hooks',
				'inner' => true,
			),
			'funktion' => array(
				'label' => esc_html__( 'Function', 'tkt-template-builder' ),
				'type'  => 'hooks',
				'inner' => true,
			),
		);

		return $shortcodes;

	}

	/**
	 * Register an array of object properties, array members to re-use as configurations.
	 *
	 * Adds Array Maps for:
	 * - 'site_infos':              Members and corresponding GUI labels of get_bloginfo.
	 * - 'user_data':               Keys of WP_User object property "data".
	 * - 'valid_operators':         Members represent valid math operatiors and their GUI label.
	 * - 'valid_comparison':        Members represent valid comparison operators and their GUI label.
	 * - 'valid_round_constants':   Members represent valid PHP round() directions and their GUI label.
	 * - 'shortcode_types':         Members represent valid ShortCode Types.
	 *
	 * @since 1.0.0
	 * @param string $map the data map to retrieve. Accepts: 'site_infos', 'user_data', 'valid_operators', 'valid_comparison', 'valid_round_constants', 'shortcode_types'.
	 * @return array $$map The Array Map requested.
	 */
	public function data_map( $map ) {

		$shortcode_types = array(
			'templating'    => esc_html__( 'Templating', 'tkt-template-builder' ),
			'hooks'         => esc_html__( 'Hooks & Functions', 'tkt-template-builder' ),
		);

		return $$map;
	}

	/**
	 * All Sanitization Options.
	 *
	 * @since 1.0.0
	 * @return array {
	 *      Multidimensional Array keyed by Sanitization options.
	 *
	 *      @type array $sanitization_option {
	 *          Single sanitization option array, holding label and callback of sanitization option.
	 *
	 *          @type string $label Label of Sanitization option as used in GUI.
	 *          @type string $callback The callback to the Sanitization function.
	 *      }
	 * }
	 */
	private function sanitize_options() {

		$sanitization_options = array(
			'none' => array(
				'label'     => esc_html__( 'No Sanitization', 'tkt-template-builder' ),
			),
			'email' => array(
				'label'     => esc_html__( 'Sanitize Email', 'tkt-template-builder' ),
				'callback'  => 'sanitize_email',
			),
			'file_name' => array(
				'label'     => esc_html__( 'File Name', 'tkt-template-builder' ),
				'callback'  => 'sanitize_file_name',
			),
			'html_class' => array(
				'label'     => esc_html__( 'HTML Class', 'tkt-template-builder' ),
				'callback'  => 'sanitize_html_class',
			),
			'key' => array(
				'label'     => esc_html__( 'Key', 'tkt-template-builder' ),
				'callback'  => 'sanitize_key',
			),
			'meta' => array(
				'label'     => esc_html__( 'Meta', 'tkt-template-builder' ),
				'callback'  => 'sanitize_meta',
			),
			'mime_type' => array(
				'label'     => esc_html__( 'Mime Type', 'tkt-template-builder' ),
				'callback'  => 'sanitize_mime_type',
			),
			'option' => array(
				'label'     => esc_html__( 'Option', 'tkt-template-builder' ),
				'callback'  => 'sanitize_option',
			),
			'sql_orderby' => array(
				'label'     => esc_html__( 'SQL Orderby', 'tkt-template-builder' ),
				'callback'  => 'sanitize_sql_orderby',
			),
			'text_field' => array(
				'label'     => esc_html__( 'Text Field', 'tkt-template-builder' ),
				'callback'  => 'sanitize_text_field',
			),
			'textarea_field' => array(
				'label'     => esc_html__( 'Text Area', 'tkt-template-builder' ),
				'callback'  => 'sanitize_textarea_field',
			),
			'title' => array(
				'label'     => esc_html__( 'Title', 'tkt-template-builder' ),
				'callback'  => 'sanitize_title',
			),
			'title_for_query' => array(
				'label'     => esc_html__( 'Title for Query', 'tkt-template-builder' ),
				'callback'  => 'sanitize_title_for_query',
			),
			'title_with_dashes' => array(
				'label'     => esc_html__( 'Title with Dashes', 'tkt-template-builder' ),
				'callback'  => 'sanitize_title_with_dashes',
			),
			'user' => array(
				'label'     => esc_html__( 'User', 'tkt-template-builder' ),
				'callback'  => 'sanitize_user',
			),
			'url_raw' => array(
				'label'     => esc_html__( 'URL Raw', 'tkt-template-builder' ),
				'callback'  => 'esc_url_raw',
			),
			'post_kses' => array(
				'label'     => esc_html__( 'Post KSES', 'tkt-template-builder' ),
				'callback'  => 'wp_filter_post_kses',
			),
			'nohtml_kses' => array(
				'label'     => esc_html__( 'NoHTML KSES', 'tkt-template-builder' ),
				'callback'  => 'wp_filter_nohtml_kses',
			),
			'absint' => array(
				'label'     => esc_html__( 'Integer', 'tkt-template-builder' ),
				'callback'  => 'absint',
			),
			'intval' => array(
				'label'     => esc_html__( 'Integer', 'tkt-template-builder' ),
				'callback'  => 'intval',
			),
			'floatval' => array(
				'label'     => esc_html__( 'Float', 'tkt-template-builder' ),
				'callback'  => 'floatval',
			),
			'is_bool' => array(
				'label'     => esc_html__( 'Is Boolean', 'tkt-template-builder' ),
				'callback'  => 'is_bool',
			),
			'boolval' => array(
				'label'     => esc_html__( 'Boolean Value', 'tkt-template-builder' ),
				'callback'  => 'boolval',
			),
		);

		return $sanitization_options;

	}

	/**
	 * Provide a public facing method to add ShortCodes to the TukuToi ShortCodes library
	 *
	 * Adds ShortCodes to `tkt_scs_register_shortcode` Filter.
	 *
	 * @since 2.0.0
	 * @param array $external_shortcodes The array of shortcodes being added.
	 * @return array $$external_shortcodes The ShortCodes array.
	 */
	public function declare_shortcodes_add_filter( $external_shortcodes ) {

		$external_shortcodes = array_merge( $external_shortcodes, $this->declare_shortcodes() );

		return $external_shortcodes;

	}

	/**
	 * Provide a public facing method to add ShortCode Types to the TukuToi ShortCodes GUI.
	 *
	 * Adds ShortCode Types to `tkt_scs_register_shortcode_type` Filter.
	 *
	 * @since 2.0.0
	 * @param array $external_shortcode_types The array of Shortcode Types being added.
	 * @return array $$external_shortcode_types The ShortCode Types array.
	 */
	public function declare_shortcodes_types_add_filter( $external_shortcode_types ) {

		$external_shortcode_types = array_merge( $external_shortcode_types, $this->data_map( 'shortcode_types' ) );

		return $external_shortcode_types;

	}

}
