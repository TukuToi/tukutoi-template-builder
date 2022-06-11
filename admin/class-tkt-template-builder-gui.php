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
				$explanation = __( 'What template to insert', 'tkt-template-builder' );
				return $explanation;
			}
		);

	}

	/**
	 * Create a Select Field set for the ShortCodes Forms SiteInfo Display Options.
	 *
	 * @since 1.4.0
	 */
	public function allmenus_options() {

		$menu_objects = wp_get_nav_menus();

		foreach ( $menu_objects as $key => $object ) {
			$label = $object->name;
			$slug  = $object->slug;
			$selected = '' === $object->slug ? 'selected' : '';
			printf( '<option value="%s" ' . esc_attr( $selected ) . '>%s</option>', esc_attr( $slug ), esc_html( $label ) );
		}

		add_filter(
			'tkt_scs_shortcodes_fieldset_explanation',
			function( $explanation ) {
				$explanation = __( 'What Menu to Show', 'tkt-template-builder' );
				return $explanation;
			}
		);

	}

	/**
	 * Create a Select Field set for the ShortCodes Forms SiteInfo Display Options.
	 *
	 * @since 1.4.0
	 */
	public function alllocations_options() {

		$locations = get_registered_nav_menus();

		foreach ( $locations as $slug => $label ) {
			$selected = '' === $slug ? 'selected' : '';
			printf( '<option value="%s" ' . esc_attr( $selected ) . '>%s</option>', esc_attr( $slug ), esc_html( $label ) );
		}

		add_filter(
			'tkt_scs_shortcodes_fieldset_explanation',
			function( $explanation ) {
				$explanation = __( 'What Menu Location to use (Wins over Menu setting above, if set)', 'tkt-template-builder' );
				return $explanation;
			}
		);

	}

	/**
	 * Create a Select Field set for the ShortCodes Forms SiteInfo Display Options.
	 *
	 * @since 1.4.0
	 */
	public function allitemspacing_options() {

		$spacings = array(
			'preserve' => 'Preserve',
			'discard'  => 'Discard',
		);

		foreach ( $spacings as $spacing => $label ) {

			$selected = 'preserve' === $spacing ? 'selected' : '';
			printf( '<option value="%s" ' . esc_attr( $selected ) . '>%s</option>', esc_attr( $spacing ), esc_html( $label ) );
		}

		add_filter(
			'tkt_scs_shortcodes_fieldset_explanation',
			function( $explanation ) {
				$explanation = __( 'Whether to preserve whitespace within the menu\'s HTML.', 'tkt-template-builder' );
				return $explanation;
			}
		);

	}

	/**
	 * Create a Select Field set for the ShortCodes Forms SiteInfo Display Options.
	 *
	 * @since 1.4.0
	 */
	public function allsidebars_options() {

		global $wp_registered_sidebars;

		if ( empty( $wp_registered_sidebars ) ) {
			return;
		}

		foreach ( $wp_registered_sidebars as $key => $sidebar ) {

			$selected = '' === $sidebar['id'] ? 'selected' : '';
			printf( '<option value="%s" ' . esc_attr( $selected ) . '>%s</option>', esc_attr( $sidebar['id'] ), esc_html( $sidebar['name'] ) );
		}

		add_filter(
			'tkt_scs_shortcodes_fieldset_explanation',
			function( $explanation ) {
				$explanation = __( 'What sidebar to include.', 'tkt-template-builder' );
				return $explanation;
			}
		);

	}

	/**
	 * Create a Select Field set for the ShortCodes Forms SiteInfo Display Options.
	 *
	 * @since 1.4.0
	 */
	public function allwidgets_options() {

		global $wp_widget_factory;

		if ( empty( $wp_widget_factory ) ) {
			return;
		}

		foreach ( $wp_widget_factory->widgets as $widget => $object ) {

			$selected = '' === $widget ? 'selected' : '';
			printf( '<option value="%s" ' . esc_attr( $selected ) . '>%s</option>', esc_attr( $widget ), esc_html( $object->widget_options['description'] ) );
		}

		add_filter(
			'tkt_scs_shortcodes_fieldset_explanation',
			function( $explanation ) {
				$explanation = __( 'What widget to include.', 'tkt-template-builder' );
				return $explanation;
			}
		);

	}

}
