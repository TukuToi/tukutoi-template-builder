<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.tukutoi/
 * @since      0.0.1
 * @package    Plugins\TemplateBuilder\Public
 * @author     Beda Schmid <beda@tukutoi.com>
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks to
 * enqueue the public-facing stylesheet and JavaScript.
 * As you add hooks and methods, update this description.
 *
 * @package    Plugins\TemplateBuilder\Public
 * @author     Beda Schmid <beda@tukutoi.com>
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
		$this->version       = $version;

	}

	/**
	 * Apply Content Template.
	 *
	 * Let's do some trickery with filters.
	 * Preamble:
	 * We add this filter apply_content_template to the content late, at priority 999.
	 * This because we want to overwrite any possible template output of the_content with the user's custom template.
	 *
	 * However, to parse the_content properly (ShortCodes, style, etc) we have to re-apply the_content filter.
	 * If we do that, we end in an infinite loop, because we add the apply_content_template filter to the_content,
	 * which will hook apply_content_template, and then inside itself, applying the_content filter, which in turn re-adds
	 * apply_content_template, which in turn reapplies the_content, and in turn re-adds apply_content_template.... you get it.
	 *
	 * Maximum level of nested reached will be the result. In other words, a timeout/fatal.
	 *
	 * To resolve this we:
	 * - add_filter `apply_content_template` to `the_content` hook
	 * - remove_filter `apply_content_template` filter from `the_content` while `apply_content_template` executes
	 * - apply `the_content` filter on the Post Content inside `apply_content_template`
	 * - add_filter `apply_content_template` back to `the_content` hook
	 * - return the Post Content.
	 *
	 * @since    1.3.0
	 * @param mixed $content The Post Content.
	 */
	public function apply_content_template( $content ) {

		// Return immediately if there is no reason to replace content.
		if ( ! is_singular() ) {
			return $content;
		}

		global $post;

		// Get all available Content Templates.
		$available_content_templates = $this->get_available_content_templates();

		// No need to proceed if there is no Content Template for this type.
		if ( ! is_array( $available_content_templates )
			|| is_null( $post )
			|| ! is_object( $post )
			|| empty( $available_content_templates )
			|| ! isset( $available_content_templates[ $post->post_type ] )
		) {
			return $content;
		}

		/**
		 * Unhook our filter.
		 */
		remove_filter( 'the_content', array( $this, 'apply_content_template' ), 999 );

		/**
		 * $this->apply_content_template is now removed from the_content.
		 * Get our Post Content, pass it thru the_content Filter, apply the content template
		 */
		$content_template_id = $available_content_templates[ $post->post_type ];
		$content             = apply_filters( 'the_content', get_post( $content_template_id )->post_content );

		/**
		 * Re-Hook our filter.
		 */
		add_filter( 'the_content', array( $this, 'apply_content_template' ), 999 );

		// Happily return ever after.
		return $content;

	}

	/**
	 * Include Custom Template.
	 *
	 * @since    0.0.1
	 * @param string $template The Template path loaded.
	 */
	public function include_template( $template ) {

		$available_templates = $this->get_available_templates();

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
			|| ( is_archive()
				&& $template_id = $available_templates['archive_template'] ?? null
			)
			|| ( is_post_type_archive()
				&& $template_id = $available_templates['post_type_archive_template'] ?? null
			)
			|| ( is_post_type_archive()
				&& $template_id = $available_templates[ get_queried_object()->slug . '_archive_template' ] ?? null
			)
			|| ( is_tax()
				&& $template_id = $available_templates['tax_template'] ?? null
			)
			|| ( is_tax()
				&& $template_id = $available_templates[ get_queried_object()->taxonomy . '_tax_template' ] ?? null
			)
			|| ( is_attachment()
				&& $template_id = $available_templates['attachment_template'] ?? null
			)
			|| ( is_singular()// is_single and is_page are both true in is_singular, after all.
				&& $template_id = $available_templates['singular_template'] ?? null
			)
			|| ( is_singular()// is_single and is_page are both true in is_singular, after all.
				&& $template_id = $available_templates[ get_post_type() . '_singular_template' ] ?? null
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
			|| ( is_year()
				&& $template_id = $available_templates['year_template'] ?? null
			)
			|| ( is_month()
				&& $template_id = $available_templates['month_template'] ?? null
			)
			|| ( is_day()
				&& $template_id = $available_templates['day_template'] ?? null
			)
		) {

			$this->template_id = $template_id;
			/**
			 * We need to add some special items to wp_kses, because they might be needed in header and footer.
			 * We cannot hook them only when header/footers are called, since a Template might include its header
			 * thru a Template ShortCode, and thus get rendered at once, not separately as when assigned thru the GUI.
			 *
			 * Thus we add the extra tags to wp_kses, then the_content filters run with it applied, and right after the
			 * content is done, we remove the tags again. We hook in on wp_kses_allowed_html, and hook out on the_content.
			 */
			add_filter( 'wp_kses_allowed_html', array( $this, 'add_special_tags_to_wp_kses' ), 10, 2 );
			add_filter( 'the_content', array( $this, 'remove_special_tags_from_wp_kses' ), 11 );

			/**
			 * Allow later filters to get Template ID and Template Settings.
			 */
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
	 * @see Plugins\TemplateBuilder\Public\Partials
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
	 * @see Plugins\TemplateBuilder\Public\Partials
	 * @param int $template_id The Template ID to load. Default: array(). Accepts: valid Settings array.
	 */
	public function get_template_settings( $template_id ) {

		$template_settings = array_map( 'sanitize_key', get_post_meta( $this->template_id, '_tkt_template_settings', false )[0] );

		return $template_settings;

	}

	/**
	 * This is a Filter callback.
	 *
	 * We have to add some special HTML Tags that are needed mostly when displaying headers.
	 *
	 * @param array  $allowed_html_tags Array of allowed tags in wp_kses.
	 * @param string $context Context of wp_kses (default in this case).
	 */
	public function add_special_tags_to_wp_kses( $allowed_html_tags, $context ) {

		$allowed_html_tags['meta'] = array(
			'charset'    => true,
			'content'    => true,
			'http-equiv' => true,
			'name'       => true,
		);
		$allowed_html_tags['link'] = array(
			'rel'         => true,
			'as'          => true,
			'href'        => true,
			'type'        => true,
			'crossorigin' => true,
		);

		return $allowed_html_tags;

	}

	/**
	 * This is a Filter callback.
	 *
	 * We have to remove some special HTML Tags that where needed mostly when displaying headers.
	 *
	 * @param mixed $content The Content - do NOTHING with it here.
	 */
	public function remove_special_tags_from_wp_kses( $content ) {

		add_filter( 'wp_kses_allowed_html', array( $this, 'unset_wp_kses_tags' ), 10, 2 );

		return $content;

	}

	/**
	 * This is a Filter callback.
	 *
	 * We have to remove some special HTML Tags that where needed mostly when displaying headers.
	 *
	 * @param array  $allowed_html_tags Array of allowed tags in wp_kses.
	 * @param string $context Context of wp_kses (default in this case).
	 */
	public function unset_wp_kses_tags( $allowed_html_tags, $context ) {

		unset( $allowed_html_tags['meta'] );
		unset( $allowed_html_tags['link'] );

		return $allowed_html_tags;

	}

	/**
	 * Load the TukuToi Template.
	 */
	private function load_template() {

		$template_path = plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tkt-template-builder-public-display.php';

		return $template_path;

	}

	/**
	 * Get Available Templates.
	 */
	private function get_available_templates() {

		$available_templates = array_map( 'sanitize_key', (array) get_option( 'tkt_available_templates', array() ) );

		return $available_templates;

	}

	/**
	 * Get Available Templates.
	 */
	private function get_available_content_templates() {

		$available_content_templates = array_map( 'sanitize_key', (array) get_option( 'tkt_available_content_templates', array() ) );

		return $available_content_templates;

	}

}
