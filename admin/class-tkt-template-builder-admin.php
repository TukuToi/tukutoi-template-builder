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
 * - enqueues all styles and scripts for admin area
 * - alters TinyMCE
 * - Add, Remove, Populate, Save metaboxes
 * - Add Template CPT
 * - Add Template CPT admin list columns
 * - Remove row actions in admin list
 * - Add Help Tabs
 * - Add ShortCodes to TukuToi ShortCodes GUI
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
		 * Array of Post types where we can apply a Content Template or Single/Archive Template.
		 * We try to remove all Post Types that we know shouldn't be used.
		 *
		 * We still allow users or plugins to manipulate the supported Post types,
		 * after all our operations. So if, then can.
		 */
		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		/**
		 * Archives of Post Types where we can apply a Template
		 */
		$post_type_archive_templates = array();

		/**
		 * Archives of Taxonomies where we can apply a Template
		 */
		$tax_archive_templates = array();

		$disallowed_post_types = array(
			'tkt_tmplt_bldr_templ',
			'attachment',
		);
		foreach ( $post_types as $key => $object ) {
			if ( in_array( $object->name, $disallowed_post_types ) ) {
				unset( $post_types[ $key ] );
			}
		}
		$post_types = apply_filters( 'tkt_tmplt_bldr_supported_post_types', $post_types );

		/**
		 * Array of Taxonomies where we can apply an archive to.
		 * We try to remove all Taxonomies that we know shouldn't be used, as well as Categories and Tags,
		 * since those have separate Templates and Template Tags.
		 *
		 * We still allow users or plugins to manipulate the supported Taxonomies,
		 * after all our operations. So if, then can.
		 */
		$taxonomies = get_taxonomies(
			array(
				'public' => true,
				'_builtin' => false,
			),
			'objects'
		);
		$disallowed_taxonomies = array(); // Currently empty.
		foreach ( $taxonomies as $key => $object ) {
			if ( in_array( $object->name, $disallowed_taxonomies ) ) {
				unset( $taxonomies[ $key ] );
			}
		}
		$taxonomies = apply_filters( 'tkt_tmplt_bldr_supported_taxonomies', $taxonomies );

		/**
		 * Array of available Templates.
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
		 * We split the templates and merge them several times on purpose, so we can control the order
		 * of the single items in the select2 instance.
		 *
		 * We inject OptGroups into the arrays to later use as optgroups for the Select2.
		 * Perehaps not the cleanest way, but works, and thus ain't stupid.
		 * OptGroups Open tags are keyed by n-increasing => Label.
		 * OptGroups closing tags are keyed by optgroupend => ''.
		 *
		 * @todo move this to a Declarations/Comfig file.
		 */
		$templates = array(
			0                               => esc_html__( 'Generic Templates', 'tkt-template-builder' ),
			'index_template'                => esc_html__( 'Everything (Index)', 'tkt-template-builder' ),
			'global_header'                 => esc_html__( 'Global Header', 'tkt-template-builder' ),
			'global_footer'                 => esc_html__( 'Global Footer', 'tkt-template-builder' ),
			'singular_template'             => esc_html__( 'All Single Pages, Posts & Custom Posts', 'tkt-template-builder' ),
			'optgroupend'                   => '',
			1                               => esc_html__( 'Particular Templates', 'tkt-template-builder' ),
			'attachment_template'           => esc_html__( 'Attachments', 'tkt-template-builder' ),
			'embed_template'                => esc_html__( 'Embeds', 'tkt-template-builder' ),
			'frontpage_template'            => esc_html__( 'Front Page', 'tkt-template-builder' ),
			'home_template'                 => esc_html__( 'Home Page', 'tkt-template-builder' ),
			'privacypolicy_template'        => esc_html__( 'Privacy Policy Page', 'tkt-template-builder' ),
			'404_template'                  => esc_html__( '404 Page', 'tkt-template-builder' ),
		);

		/**
		 * Merge Post Types into $templates for single_ and archive_ templates
		 */
		foreach ( $post_types as $key => $object ) {
			// Translators: s1 Is a Post Type Name.
			$post_type_single_templates[ $object->name . '_singular_template' ] = sprintf( esc_html__( 'Single %s' ), $object->label );
			// Exclude Pages and Posts from Archives.
			if ( false === $object->_builtin ) {
				// Translators: s1 Is a Post Type Name.
				$post_type_archive_templates[ $object->name . '_archive_template' ] = sprintf( esc_html__( '%s (Post Archives)' ), $object->label );
			}
		}
		$templates = array_merge( $templates, $post_type_single_templates );
		$templates = array_merge(
			$templates,
			array(
				'optgroupend'                   => '',
				2                               => esc_html__( 'Archive Templates', 'tkt-template-builder' ),
				'archive_template'              => esc_html__( 'All Archives (Post, Author, Taxonomy, etc)', 'tkt-template-builder' ),
				'post_type_archive_template'    => esc_html__( 'All Custom Post Type Archives', 'tkt-template-builder' ),
			),
			$post_type_archive_templates,
			array(
				'category_template'             => esc_html__( 'Category Archives', 'tkt-template-builder' ),
				'tag_template'                  => esc_html__( 'Tag Archives', 'tkt-template-builder' ),
				'tax_template'                  => esc_html__( 'All Custom Taxonomy Archives', 'tkt-template-builder' ),
			)
		);

		/**
		 * Merge Taxonomies into $templates for archive_ templates
		 */
		foreach ( $taxonomies as $key => $object ) {
			// Translators: s1 Is a Post Type Name.
			$tax_archive_templates[ $object->name . '_tax_template' ] = sprintf( esc_html__( '%s (Taxonomy Archives)' ), $object->label );
		}
		$templates = array_merge( $templates, $tax_archive_templates );
		$templates = array_merge(
			$templates,
			array(
				'optgroupend' => '',
			)
		);

		/**
		 * Finally merge Date, Author and Search Templates
		 */
		$templates = array_merge(
			$templates,
			array(
				'date_template'     => esc_html__( 'All Date Archives', 'tkt-template-builder' ),
				'year_template'     => esc_html__( 'Year Archives', 'tkt-template-builder' ),
				'month_template'     => esc_html__( 'Month Archives', 'tkt-template-builder' ),
				'day_template'     => esc_html__( 'Day Archives', 'tkt-template-builder' ),
				'author_template'   => esc_html__( 'Author Archives', 'tkt-template-builder' ),
				'search_template'   => esc_html__( 'Search Results', 'tkt-template-builder' ),
			),
		);

		/**
		 * Array of valid Templates to use as Parent Templates or other (Header, Footer) Templates.
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
	 * Add a Help Tab in the Single Template Edit Screen.
	 *
	 * @todo i18n this.
	 */
	public function help_tab_edit_template_screen() {

		$screen = get_current_screen();

		if ( ! isset( $screen->post_type ) || 'tkt_tmplt_bldr_templ' !== $screen->post_type ) {
			return;
		}
		$screen->add_help_tab(
			array(
				'id'      => 'tkt_tmplt_bldr_overview',
				'title'   => 'Template Settings',
				'content' => '<h4>Template Usage</h4><p>In WordPress, there are generally 3 types of "templates". There is what they call "Page Templates" (but really are not), then there are "Template Parts", and lastly, mandatory "parts" of a Layout, such as a Header, or a Footer.</p><p>With TukuToi Template Builder, you can design <em>all</em> of them using one tool, without the need of knowing a single line of PHP. You must understand a few things first, before you can take control over the Layout of your Website completely.</p><p>To avoid/remove the confusion that WordPress generated by calling things "Page Templates" when they are not, and generally to avoid confusion, TukuToi refers to the "entire thing" as you see it in the Front end as the "Layout of your Website". The Layout includes everything: Header, Menu, Sidebar, Content, Title, Footer, etc.<p>To Build the Layout of your Website, you will create Templates (the single parts, like a header, a footer and a main part). These Templates are what you create in the TukuToi Template Builder. Then, thru assigning them to the respective parts of your Website Layout, you can "bootstrap" these Templatse inot your final Layout.<p>For example, you can create a template and assign it to the "Global Header" Template part, which means you will include everything in that Template what usually goes in a header.php File. Then, you could create a Template and assign it to the "Taxonomy Template", so to use that Template whenever a Taxonomy Archive is shown. In this template, you would then design the "Main" part of the archive. Later, you could create the final "Global Footer" template, to complete your Layout, which would then apply whenever you visit an Archive for Taxonomies.</p><p>Similarly you can create Templates for Single Pages or Posts or Author archives, whatever is possible in WordPress.</p><h5>Using a Template to replace the Post Content</h5><p>Sometimes, you maybe want to only design the "Post Body" part of your single posts or pages, without changing anything else, like header, sidebars, footers or even Title and Author Meta. This can be achieved by assigning a Template to replace the Post Content of {specific post type}</p><h4>Parent Template.</h4><p>Sometimes you need to have a hierarchy, meaning that you design and assign a Template to some location, but then want it to "call" another Template as its parent (like a "header"). This can be achieved by setting a Parent Template. You can chain infinite Parent Templates to each other.</p><h4>Header and Footer</h4><p>These are the classic WordPress Headers and Footers. Whenever you have create a "Global Header" or "Global Footer", this setting will default to these 2 Templates. You can change it, of course, to respect either particular Headers or Footers, or call in the native Theme Header and Footer. Designing headers and footers in TukuToi Template Builder is an advanced Topic. Usually, you will probably just let the Theme do this job, and work with Parent Templates instead. However, to give you 100% control over your Website, this feature is included. Note that for this reason we need to allow advanced HTML in the Editor which is usually not allowed in WordPress. That is also why only high lever Users are allowed to edit any TukuToi Template (capability update_core and unfiltered_html are required.</p><h4>Template ShortCode.</h4><p>You may use any Template inside another Template by using its ShortCode, which youc an get from the Template Settings Sidebar in the Template Editor Screens. Note that you can not nest one Template inside the other, and then call that template again in the Template you just nested. We try to avoid that by disabling some settings whenever that happens but for ShortCodes, we cannot control where you insert them. Thus, it is suggested to use this feature with care.</p>',
			)
		);
		$screen->add_help_tab(
			array(
				'id'      => 'tkt_tmplt_bldr_codemirror',
				'title'   => 'CodeMirror',
				'content' => '<h4>Here a few shortcuts and hints for the CodeMirrr.</h4><li>Search Content: <code>Ctrl-F / Cmd-F</code></li><li>Find Next: <code>Ctrl-G / Cmd-G</code></li><li>Find Previous: <code>Shift-Ctrl-G / Shift-Cmd-G</code></li><li>Replcae: <code>Shift-Ctrl-F / Cmd-Option-F</code></li><li>Replace All: <code>Shift-Ctrl-R / Shift-Cmd-Option-F</code></li><li>Persistent Search: <code>Alt-F</code> (dialog doesn\'t autoclose, enter to find next, Shift-Enter to find previous)</li><li>Jump To Line: <code>Alt-G</code></li><h4>CodeMirror Syntax Highlighting.</h4><p>TukuToi Template Builder uses an advanced, custom version of Syntax Highlighting, so not only HTML is recognised but as well ShortCodes. In general, you will want to be thorough and resolve any warning (appearing in the numbered ruler of the CodeMirror), as well as paying attention to any "red" highlighting inside the editor.</p><p>For Example, in the following ScreenShot you can see how after <code>show=post_title"</code> there is a lot of "red" highlighted syntax, but no error is thrown in the numbered Ruler. This is because CodeMirror does not natively recognize this ShortCode syntax, and to denote the error (there is a missing <code>"</code> right after <code>show=</code>) we chose to use a red syntax highlighting for the following syntax.</p><p><img src="' . plugin_dir_url( __FILE__ ) . '/img/codemirror-error-sample.png" alt="CodeMirror Syntax Highlighter Error Sample" width="" height=""></p><p>On the other hand, sometimes CodeMirror will throw errors that, while fully justified, should be ignored. An example is when you create a Global Header, and must open the <code><body></code> tag in it, but cannot close it in the same template, as you will only close it in the Footer Template, or perhaps leave it to the theme to close that tag. In those cases, CodeMirror will complain that you must close the tag, while you really cannot. Thus, such errors can be ignored.</p><p><img src="' . plugin_dir_url( __FILE__ ) . '/img/codemirror-false-alarm.png" alt="CodeMirror Syntax Highlighter Error Sample" width="" height=""></p>',
			)
		);
		$screen->add_help_tab(
			array(
				'id'      => 'tkt_tmplt_bldr_misc',
				'title'   => 'Revisions & Comments',
				'content' => '<h4>Revisions & Comments</h4><p>Revisions are enabled by default so in case you save something you did not want to save, or delete somethign you did not want to delete, you can navigate back and re-instantiate the previous version. TukuToi Template Builder does not delete those Revisions at the moment. It is up to you to clean up the database from time to time.<p>Comments on the other hand are active so you and your collaborators may leave notes on the Templates about edits, reminders, or whatever else you like. These Comments are never shown in the Front End, they are just visible in the edit screen.</p>',
			)
		);
		$screen->set_help_sidebar( '<p><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 223 183.9"><defs><linearGradient id="Unbenannter_Verlauf_28" x1="36.31" y1="92.09" x2="147.67" y2="92.09" gradientUnits="userSpaceOnUse"><stop offset="0.37" stop-color="#005b97"/><stop offset="0.6" stop-color="#0075be"/></linearGradient><linearGradient id="Unbenannter_Verlauf_112" y1="91.95" x2="223" y2="91.95" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#0075be"/><stop offset="0.99" stop-color="#005b97"/></linearGradient><linearGradient id="Unbenannter_Verlauf_112-2" x1="155.94" y1="92.11" x2="171.43" y2="92.11" xlink:href="#Unbenannter_Verlauf_112"/></defs><g id="Layer_2" data-name="Layer 2"><g id="Ebene_1" data-name="Ebene 1"><path d="M147.66,91.71c.76,76.81-109.9,73.33-111.35.4C37.43,19.76,148.34,15.33,147.66,91.71Z" style="stroke:#fff;stroke-miterlimit:10;stroke-width:5px;fill:url(#Unbenannter_Verlauf_28)"/><path d="M149.48,114.09H72.85a1.34,1.34,0,0,1-1.35-1.35V71.5a1.34,1.34,0,0,1,1.35-1.34h76.63a1.34,1.34,0,0,1,1.34,1.34l-6.36,20.62,6.36,20.62A1.34,1.34,0,0,1,149.48,114.09Z" style="fill:#fff;stroke:#fff;stroke-miterlimit:10;stroke-width:5px"/><path d="M223,92c-23.44,21.17-44.95,44.46-67.07,67C104.24,211.42,6,175.67.92,102-11.64,14.34,108-37.17,163.21,32.33,182.7,52.68,202.46,72.68,223,92ZM106.48,91.9l31.39-31.28C110.43,17.12,35.78,38.11,37.18,92.32,37,147.05,111,166.67,138,123.29Zm41.18-.19c.48,21.45,32.13,21.56,32.73.12C180.12,70.23,148.1,70.18,147.66,91.71Z" style="fill:url(#Unbenannter_Verlauf_112)"/><path d="M171.43,92c.11,10.26-15.46,10.22-15.49.1C156.16,82.11,171.16,81.84,171.43,92Z" style="stroke:#fff;stroke-miterlimit:10;stroke-width:5px;fill:url(#Unbenannter_Verlauf_112-2)"/><path d="M35.83,59.62v-14a1.35,1.35,0,0,1,1.32-1.38H149.5a1.35,1.35,0,0,1,1.32,1.38v14A1.35,1.35,0,0,1,149.5,61H37.15A1.35,1.35,0,0,1,35.83,59.62Z" style="fill:#fff;stroke:#fff;stroke-miterlimit:10;stroke-width:5px"/><path d="M38.07,70.16H60.75A2.31,2.31,0,0,1,63,72.53v39.19a2.31,2.31,0,0,1-2.24,2.37H38.07a2.31,2.31,0,0,1-2.24-2.37V72.53A2.31,2.31,0,0,1,38.07,70.16Z" style="fill:#fff;stroke:#fff;stroke-miterlimit:10;stroke-width:5px"/><path d="M37.7,138.63v-14A1.35,1.35,0,0,1,39,123.24H151.37a1.35,1.35,0,0,1,1.32,1.38v14a1.35,1.35,0,0,1-1.32,1.39H39A1.35,1.35,0,0,1,37.7,138.63Z" style="fill:#fff;stroke:#fff;stroke-miterlimit:10;stroke-width:5px"/></g></g></svg></p>' );

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
