<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.tukutoi/
 * @since      0.0.1
 *
 * @package    Tkt_Template_Builder
 * @subpackage Tkt_Template_Builder/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.0.1
 * @package    Tkt_Template_Builder
 * @subpackage Tkt_Template_Builder/includes
 * @author     Your Name <hello@tukutoi.com>
 */
class Tkt_Template_Builder {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      Tkt_Template_Builder_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The unique prefix of this plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $plugin_prefix    The string used to uniquely prefix technical functions of this plugin.
	 */
	protected $plugin_prefix;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.0.1
	 */
	public function __construct() {

		if ( defined( 'TKT_TEMPLATE_BUILDER_VERSION' ) ) {

			$this->version = TKT_TEMPLATE_BUILDER_VERSION;

		} else {

			$this->version = '0.0.1';

		}

		$this->plugin_name = 'tkt-template-builder';
		$this->plugin_prefix = 'tkt_tmplt_bldr_';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Tkt_Template_Builder_Loader. Orchestrates the hooks of the plugin.
	 * - Tkt_Template_Builder_i18n. Defines internationalization functionality.
	 * - Tkt_Template_Builder_Admin. Defines all hooks for the admin area.
	 * - Tkt_Template_Builder_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tkt-template-builder-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tkt-template-builder-i18n.php';

		/**
		 * The class responsible for declaring all ShortCodes.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tkt-template-builder-declarations.php';

		/**
		 * The class responsible for Sanitizing and Validating inputs.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tkt-template-builder-sanitizer.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-tkt-template-builder-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-tkt-template-builder-public.php';

		/**
		 * The class responsible to load all common code
		 *
		 * NOTE: Loaded only once.
		 */
		if ( ! defined( 'TKT_COMMON_LOADED' ) ) {
			require_once( plugin_dir_path( dirname( __FILE__ ) ) . '/common/class-tkt-common.php' );
		}
		$this->common = Tkt_Common::get_instance();

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		$this->loader = new Tkt_Template_Builder_Loader();

		/**
		 * The class responsible for maintaining a list of Declarations of this plugin.
		 *
		 * @see {/includes/class-tkt-template-builder-declarations.php}.
		 */
		$this->declarations = new Tkt_Template_Builder_Declarations( $this->plugin_prefix, $this->version );

		/**
		 * Register ShortCodes with the TukuToi ShortCodes Plugin API.
		 *
		 * NOTE: ShortCodes are effectively ADDED only in the frontend, but we need their declaration in the backend as well.
		 *
		 * @since 2.0.0
		 */

		$this->loader->add_filter( 'tkt_scs_register_shortcode', $this->declarations, 'declare_shortcodes_add_filter' );
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Tkt_Template_Builder_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Tkt_Template_Builder_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_admin_hooks() {

		/**
		 * TukuToi Templates can not be edited by untrusted users:
		 * - cap `update_core` is required.
		 * - cap `update_core` is required.
		 * NOTE: Due to this restriction in MultiSites only superadmins can (by default) use TukuToi Templates.
		 * It is up to the user to assign the capability to other users of choice.
		 *
		 * The plugin Admin area will not initiate at all if the user does not meet the caps.
		 */
		if ( ! current_user_can( 'update_core' )
			|| ! current_user_can( 'unfiltered_html' )
			|| ! is_admin()
			|| ! is_user_logged_in()
		) {
			return;
		}

		/**
		 * The Plugin Admin Object.
		 */
		$plugin_admin = new Tkt_Template_Builder_Admin( $this->get_plugin_name(), $this->get_plugin_prefix(), $this->get_version(), $this->declarations );

		/**
		 * The Templates Edit Screen Features and Admin List Features
		 *
		 * Registers the Templates at init:11 because the plugin instantiates at init:10.
		 */
		$this->loader->add_action( 'init', $plugin_admin, 'register_template', 11 );
		$this->loader->add_filter( 'wp_editor_settings', $plugin_admin, 'modify_editor', 10, 2 );
		$this->loader->add_action( 'admin_head', $plugin_admin, 'add_metaboxes' );
		$this->loader->add_action( 'admin_head', $plugin_admin, 'remove_metaboxes', PHP_INT_MAX );
		$this->loader->add_action( 'save_post_tkt_tmplt_bldr_templ', $plugin_admin, 'save_metabox', 10, 2 );
		$this->loader->add_filter( 'manage_tkt_tmplt_bldr_templ_posts_columns', $plugin_admin, 'add_template_admin_list_columns' );
		$this->loader->add_action( 'manage_tkt_tmplt_bldr_templ_posts_custom_column', $plugin_admin, 'populate_template_admin_list_columns', 10, 2 );
		$this->loader->add_filter( 'post_row_actions', $plugin_admin, 'remove_row_actions', 10, 1 );
		$this->loader->add_action( 'load-post.php', $plugin_admin, 'help_tab_edit_template_screen' );

		/**
		 * Enqueue the backend scripts and styles.
		 */
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		/**
		 * Register ShortCode Types with the TukuToi ShortCodes Plugin API.
		 *
		 * @since 1.0.0
		 */
		$this->loader->add_filter( 'tkt_scs_register_shortcode_type', $this->declarations, 'declare_shortcodes_types_add_filter' );

		/**
		 * Add ShortCodes to the TukuToi ShortCodes Plugin GUI.
		 *
		 * @since 1.0.0
		 */
		foreach ( $this->declarations->shortcodes as $shortcode => $array ) {

			$this->loader->add_filter( "tkt_scs_{$shortcode}_shortcode_form_gui", $plugin_admin, 'add_shortcodes_to_gui', 10, 2 );

		}

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_public_hooks() {

		/**
		 * Kickoff the FrontEnd related stuff only if we really are in the FrontEnd.
		 * NOTE: is_admin is true when AJAX requests are done.
		 *
		 * This is a huge problem in WordPress and ClassicPress, because it means you cannot truly
		 * separate the front end from the backend, since when (example) autosave fires, ajax is true too,
		 * and thus is_admin + is_doing_ajax is true, both in the front and backend, when for example
		 * and ajax request happens (AJAX search or autosave or anything done with AJAX).
		 *
		 * We could simply check if $_REQUEST['action'] === 'is_doing_ajax' (which is a Custom TukuToi Action),
		 * however that would mean, any other AJAX request (perhaps a theme loads via ajax?) wouldn't work.
		 * Thus we have to exclude all and every backend native action to truly fire this code only on the frontend.
		 *
		 * Again we will likely miss some actions, specially arbitrary 3rd party backend AJAX actions.
		 * But at least we do not break the plugin, and exclude our front end stuff on most of the native actions.
		 *
		 * Reviewers:
		 * It is not possible nor required to check on nonce for the IF blocks including $_REQUEST.
		 */
		$backend_only_ajax_actions = array(
			'heartbeat', // WP HeartBeat call.
			'inline-save', // QuickEdit in posts admin columns.
			'delete-theme', // Delete a Theme.
			'delete-plugin', // Delete a/several Plugins.
			'destroy-sessions', // Log Out User everywhere else.
			'query-attachments', // WP Media inserter.
			'imgedit-preview', // WP Edit Media.
			'save-attachment', // WP Edit Media details.
			'oembed-cache', // Something related to editing media.
			'image-editor', // Editing Media.
			'save-widget', // Edit/Save Widgets.
			'widgets-order', // Edit/Save Widgets.
			'delete-comment', // Delete Comments.
			'fetch-list', // List Comments.
			'edit-comment', // Edit Comments.
			'dim-comment', // Approve Comments.
		);
		if ( is_admin()
			&& ! wp_doing_ajax() // It is not an AJAX call.
			|| ( isset( $_REQUEST['action'] )// @codingStandardsIgnoreLine
				&& ( in_array( $_REQUEST['action'], $backend_only_ajax_actions )// @codingStandardsIgnoreLine
					|| isset( $_REQUEST['doing_wp_cron'] )// @codingStandardsIgnoreLine
				)
			)
		) {
			return;
		}

		/**
		 * The class responsible for orchestrating public facing stuff.
		 *
		 * @since 1.0.0
		 */
		$plugin_public = new Tkt_Template_Builder_Public( $this->get_plugin_name(), $this->get_plugin_prefix(), $this->get_version() );

		/**
		 * The class responsible for Sanitization.
		 *
		 * @since 1.0.0
		 */
		$sanitizer = new Tkt_Template_Builder_Sanitizer( $this->plugin_prefix, $this->version, $this->declarations );

		/**
		 * The class responsible for processing ShortCodes in ShortCodes or attributes.
		 *
		 * @since 2.0.0
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-tkt-template-builder-shortcodes.php';
		$shortcodes = new Tkt_Template_Builder_Shortcodes( $this->plugin_prefix, $this->version, $this->declarations, $sanitizer, $plugin_public );

		/**
		 * The ShortCode Processor making nested and attribute ShortCodes work.
		 *
		 * Also removes WP Autop from content and excerpts.
		 */
		$processor = new Tkt_Shortcodes_Processor( $this->plugin_prefix, $this->version, $this->declarations );
		$this->loader->add_filter( 'the_content', $processor, 'pre_process_shortcodes', 5 );
		$this->loader->add_filter( 'tkt_pre_process_shortcodes', $processor, 'pre_process_shortcodes' );
		$this->loader->add_filter( 'tkt_post_process_shortcodes', $processor, 'post_process_shortcodes' );

		/**
		 * Template Router
		 *
		 * Applies templates (if any) to the_content late, and reroutes main templates early.
		 */
		$this->loader->add_filter( 'template_include', $plugin_public, 'include_template' );
		$this->loader->add_filter( 'the_content', $plugin_public, 'apply_content_template', 999 );

		/**
		 * Register ShortCode Callbacks.
		 *
		 * NOTE: They will be registered with tkt_scs_ prefixed handle (prefix of TukuToi ShortCodes Plugin).
		 */
		foreach ( $this->declarations->shortcodes as $shortcode => $array ) {

			$callback = $shortcode;
			if ( method_exists( $shortcodes, $callback ) ) {

				$this->loader->add_shortcode( 'tkt_scs_' . $shortcode, $shortcodes, $callback );

			}
		}

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.0.1
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.0.1
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The unique prefix of the plugin used to uniquely prefix technical functions.
	 *
	 * @since     0.0.1
	 * @return    string    The prefix of the plugin.
	 */
	public function get_plugin_prefix() {
		return $this->plugin_prefix;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.0.1
	 * @return    Tkt_Template_Builder_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.0.1
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
