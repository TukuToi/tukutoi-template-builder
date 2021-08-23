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
	 * @param      object $declarations    The declarations object.
	 */
	public function __construct( $plugin_name, $plugin_prefix, $version, $declarations ) {

		$this->plugin_name   = $plugin_name;
		$this->plugin_prefix = $plugin_prefix;
		$this->version       = $version;
		$this->declarations  = $declarations;

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

			wp_enqueue_style(
				'select2',
				plugin_dir_url( __FILE__ ) . 'css/select2.css',
				array(),
				'4.1.0-rc.0',
				'screen'
			);
			wp_enqueue_style(
				$this->plugin_name,
				plugin_dir_url( __FILE__ ) . 'css/tkt-template-builder-admin.css',
				array(
					'wp-codemirror',
					'select2',
				),
				$this->version,
				'screen'
			);

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

			wp_enqueue_script(
				'select2',
				plugin_dir_url( __FILE__ ) . 'js/select2.js',
				array(
					'jquery',
				),
				'4.1.0-rc.0',
				true
			);
			wp_enqueue_script(
				$this->plugin_name,
				plugin_dir_url( __FILE__ ) . 'js/tkt-template-builder-admin.js',
				array(
					'csslint',
					'htmlhint',
					'jshint',
					'wp-codemirror',
					'quicktags',
					'select2',
				),
				$this->version,
				true
			);

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

		add_meta_box( 'tkt_template_settings', __( 'TukuToi Template Settings', 'tkt-template-builder' ), array( $this, 'template_settings_metabox' ), 'tkt_tmplt_bldr_templ', 'side', 'high', null );

	}

	/**
	 * Callback to add Metabox
	 *
	 * @param object $post The Current Post Object.
	 * @param array  $metabox The Metabox Array.
	 */
	public function template_settings_metabox( $post, $metabox ) {

		// Existing Template Options (available templates and assignements).
		$template_options = $this->get_template_options();

		// Existing Content Template Options (available templates and assignements).
		$ct_options = $this->get_ct_options();

		// Existing Template Settings.
		$settings = $this->get_settings( $post->ID );

		/**
		 * Array of valid Templates.
		 *
		 * Note: WP get_page_templates() doc comments state that this function can be used to
		 * get all available Theme Templates, inclusive Header and Footer. That is however untrue.
		 * NO Theme in the world will add a `Template Name: name of template` to its header or footer
		 * because that would mean the users can then assign it to a page. Thus, the method can NOT be used
		 * to get all theme templates, but only those the Theme explicitly declares as a template.
		 * Not even Flagship Theme 2021 does have such a Template. Thus, we have to use a custom array and
		 * cannot rely on the Theme's available templates.
		 * This also means that we will NOT detect any Theme templates, but only support a hardcoded list thereof.
		 *
		 * @todo move this to a Declarations/Comfig file.
		 */
		$templates = array(
			'404_template'              => esc_html__( '404 Template', 'tkt-template-builder' ),
			'archive_template'          => esc_html__( 'Archive Template', 'tkt-template-builder' ),
			'attachment_template'       => esc_html__( 'Attachment Template', 'tkt-template-builder' ),
			'author_template'           => esc_html__( 'Author Template', 'tkt-template-builder' ),
			'category_template'         => esc_html__( 'Category Template', 'tkt-template-builder' ),
			'date_template'             => esc_html__( 'Date Template', 'tkt-template-builder' ),
			'embed_template'            => esc_html__( 'Embed Template', 'tkt-template-builder' ),
			'frontpage_template'        => esc_html__( 'Front Page Template', 'tkt-template-builder' ),
			'home_template'             => esc_html__( 'Home Template', 'tkt-template-builder' ),
			'index_template'            => esc_html__( 'Index Template', 'tkt-template-builder' ),
			'page_template'             => esc_html__( 'Page Template', 'tkt-template-builder' ),
			'paged_template'            => esc_html__( 'Paged Template', 'tkt-template-builder' ),
			'privacypolicy_template'    => esc_html__( 'Privacy Policy Template', 'tkt-template-builder' ),
			'search_template'           => esc_html__( 'Search Template', 'tkt-template-builder' ),
			'single_template'           => esc_html__( 'Single Template', 'tkt-template-builder' ),
			'singular_template'         => esc_html__( 'Singular Template', 'tkt-template-builder' ),
			'tag_template'              => esc_html__( 'Tags Template', 'tkt-template-builder' ),
			'taxonomy_template'         => esc_html__( 'Taxonomy Template', 'tkt-template-builder' ),
			'global_header'             => esc_html__( 'Global Header', 'tkt-template-builder' ),
			'global_footer'             => esc_html__( 'Global Footer', 'tkt-template-builder' ),
		);

		/**
		 * Array of Post types where we can apply a Content Template.
		 */
		$post_types = get_post_types( array( 'public' => true ), 'objects' );

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
			'post__not_in'  => array( $post->ID ), // avoid assigning self to self.
			'meta_query' => array(
				'relation' => 'OR',
				array( // avoid requiring template as header, footer or parent, if the other template already requires self.
					'key'     => '_tkt_template_settings',
					'value'   => $post->ID,
					'compare' => 'NOT LIKE',
				),
				array( // if there's no settings for the template yet, we can show it.
					'key'     => '_tkt_template_settings',
					'compare' => 'NOT EXISTS',
				),
			),
		);
		$tkt_templates = get_posts( $args );

		/**
		 * Did you know that if you where to add a echo $var here, where $var is just whatever comes from the included file
		 * WPCS would flag it as unsafe? So why would include() be safer? It is the exact same content. Even the exact same
		 * operation. We echo things. And yet you can avoid the WPCS flag by simply not echoing, but including.
		 * You could go a step further, and ob_get_clean the included content. Then echo. Same same... and yet WPCS would flag.
		 * This is absurd.
		 */
		include( plugin_dir_path( __FILE__ ) . 'partials/tkt-template-builder-admin-display.php' );

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

		// No need to proceed if there is no post id for some reason.
		if ( ! isset( $_POST['ID'] ) ) {
			return;
		}

		// Sanitize POSTed ID.
		$post_id = absint( wp_unslash( $_POST['ID'] ) );

		// Setup variables.
		$template_assigned_to = array();
		$content_template_assigned_to = array();
		$available_templates = array();
		$header = '';
		$footer = '';
		$parent = '';

		if ( isset( $_POST['tkt_template_assigned_to'] ) ) {
			$template_assigned_to = array_map( 'sanitize_key', $_POST['tkt_template_assigned_to'] );
		}
		if ( isset( $_POST['tkt_content_template_assigned_to'] ) ) {
			$content_template_assigned_to = array_map( 'sanitize_key', $_POST['tkt_content_template_assigned_to'] );
		}
		if ( isset( $_POST['tkt_template_header'] ) ) {
			$header = sanitize_key( $_POST['tkt_template_header'] );
		}
		if ( isset( $_POST['tkt_template_footer'] ) ) {
			$footer = sanitize_key( $_POST['tkt_template_footer'] );
		}
		if ( isset( $_POST['tkt_template_parent'] ) ) {
			$parent = sanitize_key( $_POST['tkt_template_parent'] );
		}

		/**
		 * Create the Option storing all available Template Types with the User-made Template.
		 *
		 * This allows later to quickly fetch the right template, by template type.
		 * without having to query the posts table. Once we fetched the User-made Template ID.
		 * We can then fetch the post by ID which is much more targeted than a rough "get posts where meta field is...".
		 *
		 * Note that technically, it would be better to listen to Slugs, instead of IDs.
		 * This because on migration, the ID changes, the slug not.
		 * However, machines are around 230 times faster fetching a number than a string in mySQL databases.
		 * Thus, we use ID to map the Template, and use a custom solution on migration process.
		 */
		foreach ( $template_assigned_to as $key => $template_type ) {
			$available_templates[ $template_type ] = absint( $post_id );
		}

		/**
		 * Create the Option storing all available Content Templates.
		 * Similar to Available Template Types, we use an option array where Post Type is key, and template ID is value.
		 * This allows for quickly locating the Template without need to query the entire Posts Table.
		 */
		foreach ( $content_template_assigned_to as $key => $post_type ) {
			$available_content_templates[ $post_type ] = absint( $post_id );
		}

		/**
		 * If we simply overwrite existing options with new options, we overwrite all templates.
		 * If we merge new options into existing options, we cannot remove options.
		 * Thus we need to:
		 * - get existing options of all templates.
		 * - merge new current template options into all templates options.
		 * - find the options of this template previously saved in all templates options.
		 * - find those options of the current template to be unset.
		 * - unset those options in the new options.
		 * - save everything.
		 */
		$new_template_options = array();
		$old_template_options = $this->get_template_options();
		$new_template_options = array_merge( $old_template_options, $available_templates );
		$thiz_old_template_options = array_intersect( $old_template_options, $available_templates );
		$unset_theze_templates = array_diff_key( $thiz_old_template_options, $available_templates );
		foreach ( $unset_theze_templates as $template => $template_id ) {
			unset( $new_template_options[ $template ] );
		}

		$new_ct_options = array();
		$old_ct_options = $this->get_ct_options();
		$new_ct_options = array_merge( $old_ct_options, $available_content_templates );
		$thiz_old_ct_options = array_intersect( $old_ct_options, $available_content_templates );
		$unset_theze_cts = array_diff_key( $thiz_old_ct_options, $available_content_templates );
		foreach ( $unset_theze_cts as $ct => $ct_id ) {
			unset( $new_ct_options[ $ct ] );
		}

		// Update the new option array.
		update_option( 'tkt_available_templates', $new_template_options, true );
		update_option( 'tkt_available_content_templates', $new_ct_options, true );

		/**
		 * Build an array to store the single Template settings.
		 *
		 * This meta value is hidden, so users cannot mistakenly edit it in the admin area.
		 *
		 * Here we already know the Template ID, thus we can get it specifically out of the database.
		 */
		$template_settings = array(
			'header' => $header,
			'footer' => $footer,
			'parent' => $parent,
		);

		update_post_meta( $post_id, '_tkt_template_settings', $template_settings );
	}

	/**
	 * Remove foreign Metaboxes.
	 *
	 * We can remove Metaboxes only after they are added.
	 * This hook runs at PHP_INT_MAX.
	 *
	 * @todo There is a BUG in ClassicPress see https://github.com/ClassicPress/ClassicPress/issues/777.
	 */
	public function remove_metaboxes() {

		global $wp_meta_boxes;

		$allowed_metaboxes = array(
			'postcustom',
			'revisionsdiv',
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
		$supports = array(
			'title',
			'editor',
			'comments',
			'revisions',
		);
		$args = array(
			'label'                 => __( 'Template', 'tkt-template-builder' ),
			'description'           => __( 'Templates for Single Posts, Pages or Archives', 'tkt-template-builder' ),
			'labels'                => $labels,
			'supports'              => $supports,
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

	/**
	 * Add Columns to the Template Posts Admin List.
	 *
	 * @param array $columns The Admin Screen Columns.
	 */
	public function add_template_admin_list_columns( $columns ) {

		$columns = array(
			'cb'                => $columns['cb'],
			'title'             => __( 'Title' ),
			'assigned_to'       => __( 'Assigned To' ),
			'parent_template'   => __( 'Parent Template', 'tkt-template-builder' ),
			'header'            => __( 'Header', 'tkt-template-builder' ),
			'footer'            => __( 'Footer', 'tkt-template-builder' ),
			'date'              => __( 'Date', 'tkt-template-builder' ),
			'author'            => __( 'Author', 'tkt-template-builder' ),
		);

		return $columns;

	}

	/**
	 * Populate the Column Values.
	 *
	 * @param string $column  The Admin Screen Column.
	 * @param int    $post_id The current Post ID.
	 */
	public function populate_template_admin_list_columns( $column, $post_id ) {

		$template_options = $this->get_template_options();
		$template_settings = $this->get_settings( $post_id );
		$templates_assigned_to = array_keys( $template_options, $post_id );

		if ( 'assigned_to' === $column ) {

			$templates = implode( ', ', $templates_assigned_to );
			$templates = str_replace( '_', ' ', $templates );
			$templates = ucwords( $templates );
			echo esc_html( $templates );

		}

		if ( 'parent_template' === $column ) {

			echo esc_html( ucwords( str_replace( '_', ' ', $template_settings['parent'] ) ) );

		}

		if ( 'header' === $column ) {

			if ( ! empty( $template_settings['header'] ) ) {
				echo esc_html( get_the_title( (int) $template_settings['header'] ) );
			} else {
				esc_html_e( 'No Header', 'tkt-template-builder' );
			}
		}

		if ( 'footer' === $column ) {

			if ( ! empty( $template_settings['footer'] ) ) {
				echo esc_html( get_the_title( (int) $template_settings['footer'] ) );
			} else {
				esc_html_e( 'No Footer', 'tkt-template-builder' );
			}
		}

	}

	/**
	 * Remove some of the Template Admin List actions.
	 *
	 * @param array $actions The available default Posts Row Actions.
	 */
	public function remove_row_actions( $actions ) {

		$screen = get_current_screen();

		if ( isset( $screen->post_type ) && 'tkt_tmplt_bldr_templ' === $screen->post_type ) {

			unset( $actions['view'] );
			unset( $actions['inline hide-if-no-js'] );

		}

		return $actions;

	}

	/**
	 * Add ShortCodes to the GUI.
	 *
	 * This happens only if TukuToi ShortCodes is active.
	 *
	 * @since    1.0.0
	 * @param string $file The filepath to the ShortCode GUI Form.
	 * @param string $shortcode The ShortCode tag for which we add the GUI Form.
	 */
	public function add_shortcodes_to_gui( $file, $shortcode ) {

		if ( array_key_exists( $shortcode, $this->declarations->shortcodes ) ) {
			$file = plugin_dir_path( __FILE__ ) . 'partials/tkt-template-builder-' . $shortcode . '-form.php';
		}

		return $file;

	}

	/**
	 * Get the Options of available Templates
	 */
	private function get_template_options() {
		$options = array_map( 'sanitize_key', (array) get_option( 'tkt_available_templates', array() ) );
		return $options;
	}

	/**
	 * Get the Options of available Templates
	 */
	private function get_ct_options() {
		$options = array_map( 'sanitize_key', (array) get_option( 'tkt_available_content_templates', array() ) );
		return $options;
	}

	/**
	 * Get the Options of a specific Template
	 *
	 * @param int $template_id The ID of the current edited Template.
	 */
	private function get_settings( $template_id ) {
		$template_settings = array_map( 'sanitize_key', get_post_meta( $template_id, '_tkt_template_settings' )[0] );
		return $template_settings;
	}

}
