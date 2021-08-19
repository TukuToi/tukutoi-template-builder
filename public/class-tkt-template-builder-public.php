<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.tukutoi/
 * @since      0.0.1
 *
 * @package    Tkt_Template_Builder
 * @subpackage Tkt_Template_Builder/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks to
 * enqueue the public-facing stylesheet and JavaScript.
 * As you add hooks and methods, update this description.
 *
 * @package    Tkt_Template_Builder
 * @subpackage Tkt_Template_Builder/public
 * @author     Your Name <hello@tukutoi.com>
 */
class Tkt_Template_Builder_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The unique prefix of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $plugin_prefix    The string used to uniquely prefix technical functions of this plugin.
	 */
	private $plugin_prefix;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 * @param      string $plugin_name      The name of the plugin.
	 * @param      string $plugin_prefix          The unique prefix of this plugin.
	 * @param      string $version          The version of this plugin.
	 */
	public function __construct( $plugin_name, $plugin_prefix, $version ) {

		$this->plugin_name   = $plugin_name;
		$this->plugin_prefix = $plugin_prefix;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tkt-template-builder-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tkt-template-builder-public.js', array( 'jquery' ), $this->version, true );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    0.0.1
	 * @param string $template The Template path loaded.
	 */
	public function include_template( $template ) {

		$available_templates = array(
			'404_template'      => 9122,
			'singular_template' => 1000,
			'single_template'   => 1000,
			'content_template'  => 200,
		);

		if ( ( is_embed()
			&& $template_id = $available_templates['embed_template'] ?? null
			)
			|| ( is_404()
				&& $template_id = $available_templates['404_template'] ?? null
			)
			|| ( is_search()
				&& $template_id = $available_templates['search_template'] ?? null
			)
			|| ( is_front_page()
				&& $template_id = $available_templates['front_page_template'] ?? null
			)
			|| ( is_home()
				&& $template_id = $available_templates['home_template'] ?? null
			)
			|| ( is_post_type_archive()
				&& $template_id = $available_templates['post_type_archive_template'] ?? null
			)
			|| ( is_tax()
				&& $template_id = $available_templates['tax_template'] ?? null
			)
			|| ( is_attachment()
				&& $template_id = $available_templates['attachment_template'] ?? null
			)
			|| ( is_single()
				&& $template_id = $available_templates['single_template'] ?? null
			)
			|| ( is_page()
				&& $template_id = $available_templates['page_template'] ?? null
			)
			|| ( is_singular()
				&& $template_id = $available_templates['singular_template'] ?? null
			)
			|| ( is_category()
				&& $template_id = $available_templates['category_template'] ?? null
			)
			|| ( is_tag()
				&& $template_id = $available_templates['tag_template'] ?? null
			)
			|| ( is_author()
				&& $template_id = $available_templates['author_template'] ?? null
			)
			|| ( is_date()
				&& $template_id = $available_templates['date_template'] ?? null
			)
			|| ( is_archive()
				&& $template_id = $available_templates['archive_template'] ?? null
			)
		) {

			$this->template_id = $template_id;
			add_filter( 'tkt_template_id', array( $this, 'get_template_id' ), 10, 1 );
			add_filter( 'tkt_template_settings', array( $this, 'get_template_settings' ), 10, 1 );

			$template = $this->load_template( $template_id );

		}

		return $template;
	}

	/**
	 * This is a Filter callback.
	 *
	 * It allows to set the Template ID in $this->include_template()
	 * We then get these settings with:
	 * `$template_id = apply_filters( 'tkt_template_id', 0 );`
	 *
	 * @see {/tukutoi-template-builder/public/partials/tkt-template-builder-public-display.php}
	 * @param int $template_id The Template ID to load. Default: array(). Accepts: valid Settings array.
	 */
	public function get_template_id( $template_id ) {

		return $this->template_id;

	}

	/**
	 * This is a Filter callback.
	 *
	 * It allows to set the Template settings in $this->include_template()
	 * We then get these settings with:
	 * `$template_id = apply_filters( 'tkt_template_settings', array() );`
	 *
	 * @see {/tukutoi-template-builder/public/partials/tkt-template-builder-public-display.php}
	 * @param int $template_id The Template ID to load. Default: array(). Accepts: valid Settings array.
	 */
	public function get_template_settings( $template_id ) {

		// get the template settings here.
		$template_settings = array(
			'header' => 1230000,
			'footer' => null,
			'type'   => 'single', // archive, single, yada.
		);

		return $template_settings;

	}

	/**
	 * Load the TukuToi Template.
	 */
	private function load_template() {

		$template_path = plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tkt-template-builder-public-display.php';

		return $template_path;

	}

}
