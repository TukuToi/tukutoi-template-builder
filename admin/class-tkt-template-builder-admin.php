<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.tukutoi/
 * @since      0.0.1
 *
 * @package    Tkt_Template_Builder
 * @subpackage Tkt_Template_Builder/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks to
 * enqueue the admin-facing stylesheet and JavaScript.
 * As you add hooks and methods, update this description.
 *
 * @package    Tkt_Template_Builder
 * @subpackage Tkt_Template_Builder/admin
 * @author     Your Name <hello@tukutoi.com>
 */
class Tkt_Template_Builder_Admin {

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
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $plugin_prefix    The unique prefix of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $plugin_prefix, $version ) {

		$this->plugin_name   = $plugin_name;
		$this->plugin_prefix = $plugin_prefix;
		$this->version       = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.0.1
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_styles( $hook_suffix ) {

		// $hook_suffix is useless, it only tells this is a post (any) edit screen.
		$screen = get_current_screen();
		if ( isset( $screen->post_type ) && 'tkt_tmplt_bldr_templ' === $screen->post_type ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tkt-template-builder-admin.css', array( 'wp-codemirror' ), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * Dependencies:
	 * - csslint
	 * - htmlhint
	 * - jshint
	 * - wp-codemirror
	 * - quicktags
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_scripts( $hook_suffix ) {

		// $hook_suffix is useless, it only tells this is a post (any) edit screen.
		$screen = get_current_screen();
		if ( isset( $screen->post_type ) && 'tkt_tmplt_bldr_templ' === $screen->post_type ) {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tkt-template-builder-admin.js', array( 'csslint', 'htmlhint', 'jshint', 'wp-codemirror', 'quicktags' ), $this->version, true );
		}

	}

	/**
	 * Modify the native WP Editor
	 *
	 * @see https://docs.classicpress.net/reference/hooks/wp_editor_settings/
	 *
	 * @param array $settings    Array of editor arguments. Available settings @see https://docs.classicpress.net/reference/classes/_WP_Editors/parse_settings/.
	 * @param int   $editor_id   ID for the current editor instance.
	 */
	public function modify_editor( $settings, $editor_id ) {

		// $hook_suffix is useless, it only tells this is a post (any) edit screen.
		$screen = get_current_screen();
		if ( 'content' === $editor_id && isset( $screen->post_type ) && 'tkt_tmplt_bldr_templ' === $screen->post_type ) {
			$settings['tinymce']   = false;
			$settings['quicktags'] = array(
				'buttons' => 'link',
			);
		}

		return $settings;
	}

	/**
	 * Add Metaboxe to the Template Editor Screen.
	 *
	 * @see https://docs.classicpress.net/reference/functions/add_meta_box/
	 */
	public function add_metaboxes() {

		add_meta_box( 'tkt_template_settings', __( 'TukuToi Template Settings', 'tkt-template-builder' ), array( $this, 'template_settings_metabox' ), 'tkt_tmplt_bldr_templ', 'side', 'high', array() );

	}

	/**
	 * Callback to add Metabox
	 *
	 * @param object $post The Current Post Object.
	 * @param array  $metabox The Metabox Array.
	 */
	public function template_settings_metabox( $post, $metabox ) {

		?>
		<select>
			<option>404_template</option>
			<option>archive_template</option>
			<option>attachment_template</option>
			<option>author_template</option>
			<option>category_template</option>
			<option>date_template</option>
			<option>embed_template</option>
			<option>frontpage_template</option>
			<option>home_template</option>
			<option>index_template</option>
			<option>page_template</option>
			<option>paged_template</option>
			<option>privacypolicy_template</option>
			<option>search_template</option>
			<option>single_template</option>
			<option>singular_template</option>
			<option>tag_template</option>
			<option>taxonomy_template</option>
		</select>
		<?php
		wp_nonce_field( 'save_tkt_template_settings', 'tkt_template_settings_nonce' );
	}

	/**
	 * Callback to save Metabox settings
	 *
	 * @param int    $post_id The Current Post ID.
	 * @param object $post The Current Post Object.
	 */
	public function save_metabox( $post_id, $post ) {
		/**
		 * Verify Nonce
		 */
		$nonce_name   = isset( $_POST['tkt_template_settings_nonce'] ) ? sanitize_key( wp_unslash( $_POST['tkt_template_settings_nonce'] ) ) : '';
		$nonce_action = 'save_tkt_template_settings';
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		// Save here.
	}

	/**
	 * Remove foreign Metaboxes.
	 *
	 * We can remove Metaboxes only after they are added.
	 * This hook runs at PHP_INT_MAX.
	 */
	public function remove_metaboxes() {

		global $wp_meta_boxes;

		$allowed_metaboxes = array(
			'postcustom',
			'revisionsdiv',
			'commentstatusdiv',
			'commentsdiv',
			'slugdiv',
			'submitdiv',
			'tkt_template_settings',
		);

		if ( is_array( $wp_meta_boxes ) ) {
			foreach ( $wp_meta_boxes as $screen => $types ) {
				foreach ( $types as $type => $priorities ) {
					foreach ( $priorities as $priority => $metaboxes ) {
						foreach ( $metaboxes as $metabox => $config ) {
							if ( ! in_array( $metabox, $allowed_metaboxes ) ) {
								remove_meta_box( $metabox, 'tkt_tmplt_bldr_templ', $type );
							}
						}
					}
				}
			}
		}

	}

	/**
	 * Register the Post Type acting as Template.
	 */
	public function register_template() {

		$labels = array(
			'name'                  => _x( 'Templates', 'Post Type General Name', 'tkt-template-builder' ),
			'singular_name'         => _x( 'Template', 'Post Type Singular Name', 'tkt-template-builder' ),
			'menu_name'             => __( 'Templates', 'tkt-template-builder' ),
			'name_admin_bar'        => __( 'Template', 'tkt-template-builder' ),
			'all_items'             => __( 'All Templates', 'tkt-template-builder' ),
			'add_new_item'          => __( 'Add New Template', 'tkt-template-builder' ),
			'add_new'               => __( 'Add New', 'tkt-template-builder' ),
			'new_item'              => __( 'New Template', 'tkt-template-builder' ),
			'edit_item'             => __( 'Edit Template', 'tkt-template-builder' ),
			'update_item'           => __( 'Update Template', 'tkt-template-builder' ),
			'view_item'             => __( 'View Template', 'tkt-template-builder' ),
			'view_items'            => __( 'View Templates', 'tkt-template-builder' ),
			'search_items'          => __( 'Search Template', 'tkt-template-builder' ),
			'not_found'             => __( 'Not found', 'tkt-template-builder' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'tkt-template-builder' ),
			'insert_into_item'      => __( 'Insert into Template', 'tkt-template-builder' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Template', 'tkt-template-builder' ),
			'items_list'            => __( 'Templates list', 'tkt-template-builder' ),
			'items_list_navigation' => __( 'Templates list navigation', 'tkt-template-builder' ),
			'filter_items_list'     => __( 'Filter Templates list', 'tkt-template-builder' ),
		);
		$capabilities = array(
			'edit_post'             => 'update_core',
			'read_post'             => 'update_core',
			'delete_post'           => 'update_core',
			'edit_posts'            => 'update_core',
			'edit_others_posts'     => 'update_core',
			'delete_posts'          => 'update_core',
			'publish_posts'         => 'update_core',
			'read_private_posts'    => 'update_core',
		);
		$args = array(
			'label'                 => __( 'Template', 'tkt-template-builder' ),
			'description'           => __( 'Templates for Single Posts, Pages or Archives', 'tkt-template-builder' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'comments', 'revisions', 'custom-fields' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 60,
			'menu_icon'             => 'dashicons-layout',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => false,
			'rewrite'               => false,
			'capabilities'          => $capabilities,
			'show_in_rest'          => false,
		);

		register_post_type( 'tkt_tmplt_bldr_templ', $args );

	}

}
