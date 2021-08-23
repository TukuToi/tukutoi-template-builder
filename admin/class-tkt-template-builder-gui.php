<?php
/**
 * This file includes the ShortCodes GUI interfaces.
 *
 * @since 1.4.0
 * @package Tkt_Template_Builder/admin
 */

/**
 * The class to generate a ShortCode GUI.
 *
 * Defines all type of Input fields necessary, also
 * creates specific methods to populate eventual options
 * and returns a fully usable GUI (jQuery dialog) for each ShortCode.
 *
 * @todo Move all these procedural silly single methods to a more abstract method!
 * The almost to all the same thing, unless one or two. Thus use arguments, not new methods.
 *
 * @since      1.4.0
 * @package    Tkt_Template_Builder
 * @subpackage Tkt_Template_Builder/admin
 * @author     Your Name <hello@tukutoi.com>
 */
class Tkt_Template_Builder_Gui {

	/**
	 * The Configuration object.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $declarations    All configurations and declarations of this plugin.
	 */
	private $declarations;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since   1.0.0
	 * @param   array $declarations    The Configuration object.
	 */
	public function __construct( $declarations ) {

		$this->declarations = $declarations;

	}

	/**
	 * Create a Select Field set for the ShortCodes Forms SiteInfo Display Options.
	 *
	 * @since 1.4.0
	 */
	public function alltemplates_options() {

		/**
		 * Array of valid Templates.
		 *
		 * Make sure to exclude current Template from the lists.
		 * Users sometimes have worms and try to assign things to themselves.
		 * Additionally they tray to assign this to that, and then that to this.
		 * Unfortunately we have to avoid this and that means a bit of query time.
		 */
		$args = array(
			'post_status'   => array( 'publish' ),
			'post_type'     => array( 'tkt_tmplt_bldr_templ' ),
		);
		$tkt_templates = get_posts( $args );

		foreach ( $tkt_templates as $key => $object ) {
			$label = $object->post_title;
			$id  = $object->ID;
			$selected = '' === $object->ID ? 'selected' : '';
			printf( '<option value="%s" ' . esc_attr( $selected ) . '>%s</option>', esc_attr( $id ), esc_html( $label ) );
		}

		add_filter(
			'tkt_scs_shortcodes_fieldset_explanation',
			function( $explanation ) {
				$explanation = __( 'What template to insert' );
				return $explanation;
			}
		);

	}

}
