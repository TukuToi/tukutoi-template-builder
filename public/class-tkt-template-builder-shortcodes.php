<?php
/**
 * The ShortCodes of the plugin.
 *
 * @link       https://www.tukutoi.com/
 * @since      1.3.0
 *
 * @package    Plugins\ShortCodes\TemplateBuilder
 * @author     TukuToi <hello@tukutoi.com>
 */

/**
 * Defines all ShortCodes of the TukuToi Template Builder Plugin.
 *
 * @package    ShortCodes
 * @author     TukuToi <hello@tukutoi.com>
 */
class Tkt_Template_Builder_Shortcodes {

	/**
	 * The unique prefix of this plugin.
	 *
	 * @since    1.3.0
	 * @access   private
	 * @var      string    $plugin_prefix    The string used to uniquely prefix technical functions of this plugin.
	 */
	private $plugin_prefix;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.3.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The Configuration object.
	 *
	 * @since    1.3.0
	 * @access   private
	 * @var      string    $declarations    All configurations and declarations of this plugin.
	 */
	private $declarations;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.3.0
	 * @param      string $plugin_prefix    The unique prefix of this plugin.
	 * @param      string $version          The version of this plugin.
	 * @param      object $declarations     The Configuration object.
	 * @param      object $sanitizer        The Sanitization object.
	 * @param      object $plugin_public    The Public object of this plugin.
	 */
	public function __construct( $plugin_prefix, $version, $declarations, $sanitizer, $plugin_public ) {

		$this->plugin_prefix = $plugin_prefix;
		$this->version       = $version;
		$this->declarations  = $declarations;

		$this->sanitizer     = $sanitizer;
		$this->plugin_public = $plugin_public;

	}

	/**
	 * TukuToi `[template]` ShortCode.
	 *
	 * Outputs a Template when used as ShortCode.</br>
	 *
	 * Example usage:
	 * `[template id="123"]`</br>
	 * For possible attributes see the Parameters > $atts section below or use the TukuToi ShortCodes GUI.
	 *
	 * @since    1.3.0
	 * @param array  $atts {
	 *      The ShortCode Attributes.
	 *
	 *      @type string    $id       The ID of the Template to load. Default: 0. Accepts: valid Template ID.
	 * }
	 * @param mixed  $content   ShortCode enclosed content. Not applicable for this ShortCode.
	 * @param string $tag       The Shortcode tag. Value: 'template'.
	 */
	public function template( $atts, $content = null, $tag ) {

		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			$tag
		);

		// Sanitize the User input atts.
		foreach ( $atts as $key => $value ) {
			$atts[ $key ] = $this->sanitizer->sanitize( 'absint', $value );
		}

		/**
		 * A Template is like passing $content to a ShortCode. They enclose other ShortCodes.
		 * Thus, we need to do_shortcode() and apply_filters.
		 *
		 * @since 1.3.0
		 * @todo Check if that is necessary and if we can check something like did_filter.
		 */
		$out = apply_filters( 'tkt_pre_process_shortcodes', get_post( $atts['id'] )->post_content );
		$out = do_shortcode( $out );

		/**
		 * Return our output.
		 *
		 * @todo check how we could sanitize/escape this, if even needed.
		 * Technically this is the same as the_content, thus, no sanitization or escaping needed at this point.
		 */
		return $out;

	}

	/**
	 * TukuToi `[do_action]` ShortCode.
	 *
	 * Executes an Action.</br>
	 *
	 * Example usage:
	 * `[do_action hook_name="wp_head"]`</br>
	 * For possible attributes see the Parameters > $atts section below or use the TukuToi ShortCodes GUI.
	 *
	 * @since    2.0.0
	 * @param array  $atts {
	 *      The ShortCode Attributes.
	 *
	 *      @type string    $hook_name       The action to execute. Default: ''. Accepts: valid action tag.
	 *      @type string    ...$arg          Arguments to pass. Default: ''. Accepts: comma-delimited `argument:value` pairs.
	 * }
	 * @param mixed  $content   ShortCode enclosed content. Not applicable for this ShortCode.
	 * @param string $tag       The Shortcode tag. Value: 'do_action'.
	 */
	public function do_action( $atts, $content = null, $tag ) {

		$atts = shortcode_atts(
			array(
				'hook_name' => '',
				'args'      => '',
			),
			$atts,
			$tag
		);

		// Sanitize the User input atts.
		foreach ( $atts as $key => $value ) {
			if ( 'args' === $key ) {
				$value = $this->sanitizer->sanitize( 'text_field', $value );
				$args  = array();
				if ( ! empty( $value ) ) {
					// If several args are passed.
					if ( strpos( $value, ',' ) !== false ) {
						$args_pre = explode( ',', $value );
						foreach ( $args_pre as $key => $arrval ) {
							list( $k, $v ) = explode( ':', $arrval );
							$args[ $k ]    = is_numeric( $v ) ? (int) $v : '\'' . $v . '\'';
						}
					} else {
						list( $k, $v ) = explode( ':', $value );
						$args[ $k ]    = is_numeric( $v ) ? (int) $v : '\'' . $v . '\'';
					}
				}
			} else {
				$atts[ $key ] = $this->sanitizer->sanitize( 'text_field', $value );
			}
		}

		/**
		 * Prepare the action args
		 * We support only plain strings ATM
		 *
		 * @since 1.25.0
		 */
		$action_args = null;
		if ( is_array( $args ) ) {
			$action_args = implode( ', ', $args );
		}

		/**
		 * If the Action echoes something or does something, which it should,
		 * we cannot output it directly, as that would under circumstances echo() a value inside a ShortCode.
		 * This is breaking ShortCodes. Thus, buffer the output.
		 */
		ob_start();
		if ( ! empty( $action_args ) ) {
			// Some arguments where passed.
			$out = do_action( $atts['hook_name'], $action_args );
		} else {
			// No arguments where passed.
			$out = do_action( $atts['hook_name'] );
		}
		$out = ob_get_clean();

		/**
		 * If the Output Buffer is empty, probably the action hooked is doing_it_wrong.
		 * However, we try to catch this and output directly, just in case the action returned something.
		 *
		 * @todo check what happens if an action does indeed echo, but echoes an empty string.
		 */
		if ( empty( $out ) ) {
			if ( ! empty( $action_args ) ) {
				// Some arguments where passed.
				$out = do_action( $atts['hook_name'], $action_args );
			} else {
				// No arguments where passed.
				$out = do_action( $atts['hook_name'] );
			}
		}

		/**
		 * Return our output.
		 *
		 * @todo check how we could sanitize/escape this, if even needed.
		 * Technically people would make sure of that in their add_action calls...
		 */
		return $out;

	}

	/**
	 * TukuToi `[funktion]` ShortCode.
	 *
	 * Executes a Function.</br>
	 * This ShortCode is named `funktion` not because we love German Language,
	 * but because PHP does not allow for a function with name `function` to be declared.
	 *
	 * Example usage:
	 * `[funktion function_name="my_function" args="color:red,size:big"]`</br>
	 * For possible attributes see the Parameters > $atts section below or use the TukuToi ShortCodes GUI.
	 *
	 * @since    2.0.0
	 * @param array  $atts {
	 *      The ShortCode Attributes.
	 *
	 *      @type string    $function_name   The Function to execute. Default: ''. Accepts: valid function tag.
	 *      @type string    ...$arg          Arguments to pass. Default: ''. Accepts: comma-delimited `argument:value` pairs.
	 * }
	 * @param mixed  $content   ShortCode enclosed content. Not applicable for this ShortCode.
	 * @param string $tag       The Shortcode tag. Value: 'funktion'.
	 */
	public function funktion( $atts, $content = null, $tag ) {

		$atts = shortcode_atts(
			array(
				'function_name' => '',
				'args'          => '',
			),
			$atts,
			$tag
		);

		// Sanitize the User input atts.
		foreach ( $atts as $key => $value ) {
			if ( 'args' === $key ) {
				$value = $this->sanitizer->sanitize( 'text_field', $value );
				$args  = array();
				if ( ! empty( $value ) ) {
					// If several args are passed.
					if ( strpos( $value, ',' ) !== false ) {
						$args_pre = explode( ',', $value );
						foreach ( $args_pre as $key => $arrval ) {
							list( $k, $v ) = explode( ':', $arrval );
							$args[ $k ]    = is_numeric( $v ) ? (int) $v : $v;
						}
					} else {
						list( $k, $v ) = explode( ':', $value );
						$args[ $k ]    = is_numeric( $v ) ? (int) $v : $v;
					}
				}
			} else {
				$atts[ $key ] = $this->sanitizer->sanitize( 'text_field', $value );
			}
		}

		/**
		 * Prepare the function args
		 * We support only plain strings ATM
		 *
		 * @since 1.25.0
		 */
		$function_args = null;
		if ( is_array( $args ) ) {
			$function_args = implode( ', ', $args );
		}

		/**
		 * If the Function echoes something,
		 * we cannot output it directly, as that would under circumstances echo() a value inside a ShortCode.
		 * This is breaking ShortCodes. Thus, buffer the output.
		 */
		ob_start();
		if ( ! empty( $function_args ) ) {
			// Some arguments where passed.
			$out = call_user_func( $atts['function_name'], $function_args );
		} else {
			// No arguments where passed.
			$out = call_user_func( $atts['function_name'] );
		}
		$out = ob_get_clean();

		/**
		 * If the Output Buffer is empty, probably the function did not echo anything but returned something.
		 * We try to catch this and output directly, just in case function action returned something.
		 *
		 * @todo check what happens if a function does indeed echo, but echoes an empty string.
		 */
		if ( empty( $out ) ) {
			if ( ! empty( $function_args ) ) {
				$out = call_user_func( $atts['function_name'], $function_args );
			} else {
				$out = call_user_func( $atts['function_name'] );
			}
		}

		/**
		 * Return our output.
		 *
		 * @todo check how we could sanitize/escape this, if even needed.
		 * Technically people would make sure of that in their functions...
		 */
		return $out;

	}

	/**
	 * TukuToi `[navmenu]` ShortCode.
	 *
	 * Shows a Navigation Menu.</br>
	 *
	 * Example usage:
	 * `[navmenu menu="my-menu"]`</br>
	 * For possible attributes see the Parameters > $atts section below or use the TukuToi ShortCodes GUI.
	 *
	 * @since    2.0.0
	 * @param array  $atts {
	 *      The ShortCode Attributes.
	 *
	 *     @type string $menu                Desired menu. Default: ''. Accepts: menu ID, slug, name, or object.
	 *     @type string $menu_class          CSS class to use for the ul element which forms the menu. Default 'menu'. Accepts: valid CSS class.
	 *     @type string menu_id              The ID that is applied to the ul element which forms the menu. Default is the menu slug, incremented. Accepts: valid CSS ID.
	 *     @type string container            Whether to wrap the ul, and what to wrap it with. Default 'div'. Accepts: valid HTML tag.
	 *     @type string container_class      Class that is applied to the container. Default 'menu-{menu slug}-container'. Accepts: valid CSS class.
	 *     @type string container_id         The ID that is applied to the container. Default: ''. Accepts: valid CSS ID.
	 *     @type string container_aria_label The aria-label attribute that is applied to the container when it's a nav element. Default: ''. Accepts: valid aria-label attribute value
	 *     @type string fallback_cb          If the menu doesn't exist, a callback function will fire. Default: 'wp_page_menu'. Accepts: 'false', valid callback.
	 *     @type string before               Text before the link markup. Default: ''. Accepts: valid string.
	 *     @type string after                Text after the link markup. Default: ''. Accepts: valid string.
	 *     @type string link_before          Text before the link text. Default: ''. Accepts: valid string.
	 *     @type string link_after           Text after the link text. Default: ''. Accepts: valid string.
	 *     @type int    depth                How many levels of the hierarchy are to be included. 0 means all. Default: 0. Accepts: valid integer.
	 *     @type object walker               Instance of a custom walker class. Default: ''. Accepts: valid Class Name with arguments (ClassName($arg, $arg1)).
	 *     @type string theme_location       Theme location to be used. Must be registered with register_nav_menu() in order to be selectable by the user. Default: ''. Accepts: valid Theme Location.
	 *     @type string items_wrap           How the list items should be wrapped. Uses printf() format with numbered placeholders. Default: ul with an id and class. Accepts: valid printf() format.
	 *     @type string item_spacing         Whether to preserve whitespace within the menu's HTML. Default: 'preserve'. Accepts: 'preserve' or 'discard'.
	 * }
	 * @param mixed  $content   ShortCode enclosed content. Not applicable for this ShortCode.
	 * @param string $tag       The Shortcode tag. Value: 'navmenu'.
	 */
	public function navmenu( $atts, $content = null, $tag ) {

		$atts = shortcode_atts(
			array(
				'menu'                 => '',
				'menu_class'           => 'menu',
				'menu_id'              => '',
				'container'            => 'div',
				'container_class'      => '',
				'container_id'         => '',
				'container_aria_label' => '',
				'fallback_cb'          => 'wp_page_menu',
				'before'               => '',
				'after'                => '',
				'link_before'          => '',
				'link_after'           => '',
				'depth'                => 0,
				'walker'               => '',
				'theme_location'       => '',
				'item_spacing'         => 'preserve',
			),
			$atts,
			$tag
		);

		/**
		 * There are some nav menu args that we shouldnt change in any case.
		 */
		$defaults = array(
			'echo' => false,
		);

		// Sanitize the User input atts.
		foreach ( $atts as $key => $value ) {
			if ( 'depth' === $key ) {
				$atts[ $key ] = $this->sanitizer->sanitize( 'absint', $value );
			} elseif ( 'items_wrap' === $key ) {
				$atts[ $key ] = $this->sanitizer->sanitize( 'wp_kses_post', $value );
			} else {
				$atts[ $key ] = $this->sanitizer->sanitize( 'text_field', $value );
			}
		}

		/**
		 * Instantiate the Custom Walker.
		 *
		 * TukuToi Template Builder offers a Bootstrap 4 walker.
		 * Test if it is required, or else instantiate the eventual custom class.
		 */
		$classname = $atts['walker'];
		if ( 'WP_Bootstrap_Navwalker' === $classname ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-bootstrap-navwalker.php';
		}
		if ( ! empty( $atts['walker'] ) && class_exists( $atts['walker'] ) ) {
			$atts['walker'] = new $classname();
		}

		// Merge user args and defaults.
		$args = wp_parse_args( $atts, $defaults );

		// Create the Navigation Menu.
		$out = wp_nav_menu( $args );

		// Output the navigation menu.
		return $out;

	}

	/**
	 * TukuToi `[widget]` ShortCode.
	 *
	 * Displays a Widget.</br>
	 *
	 * Example usage:
	 * `[widget widget="WP_Widget_Archives" title="Your Archives" count="1" dropdown="1"]`</br>
	 * For possible attributes see the Parameters > $atts section below or use the TukuToi ShortCodes GUI.
	 *
	 * @since    2.0.0
	 * @param array  $atts {
	 *      The ShortCode Attributes.
	 *
	 *      @type string    $widget        The Widget to display. Default: ''. Accepts: valid Widget ClassName.
	 *      @type string    $before_widget HTML content that will be prepended to the widget's HTML output. Default: '<div class="widget %s">' (where %s is widget's class name). Accepts: valid HTML opening tag.
	 *      @type string    $after_widget  HTML content that will be appended to the widget's HTML output. Default: '</div>'. Accepts: valid HTML closing tag.
	 *      @type string    $before_title  HTML content that will be prepended to the widget's title when displayed. Default '<h2 class="widgettitle">'. Accepts: valid HTML opening tag.
	 *      @type string    $after_title   HTML content that will be appended to the widget's title when displayed. Default </h2>. Accepts: valid HTML closing tag.
	 * }
	 * @param mixed  $content   ShortCode enclosed content. Not applicable for this ShortCode, <strong><em>unless</em></strong> for 'WP_Widget_Custom_HTML' and 'WP_Widget_Text', where the wrapped $content will be the content of the HTML or Text widget.
	 * @param string $tag       The Shortcode tag. Value: 'widget'.
	 */
	public function widget( $atts, $content = null, $tag ) {

		$atts = shortcode_atts(
			array(
				'widget'        => '',
				'classname'     => '',
				'before_widget' => '<div class="widget %s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="widgettitle">',
				'after_title'   => '</h2>',
				'title'         => '',
				'count'         => 0,
				'dropdown'      => 0,
				'hierarchical'  => 0,
				'category'      => false,
				'description'   => '',
				'rating'        => '',
				'images'        => '',
				'name'          => '',
				'sortby'        => 'menu_order',
				'exclude'       => null,
				'number'        => 5,
				'url'           => '',
				'items'         => -1,
				'show_summary'  => false,
				'show_author'   => false,
				'show_date'     => false,
				'taxonomy'      => 'post_tag',
				'filter'        => '',
				'nav_menu'      => '',
			),
			$atts,
			$tag
		);

		// Sanitize the User input atts.
		foreach ( $atts as $key => $value ) {
			if ( 'before_widget' === $key || 'after_widget' === $key || 'before_title' === $key || 'after_title' === $key ) {
				$atts[ $key ] = $this->sanitizer->sanitize( 'wp_kses_post', $value );
			} elseif ( 'count' === $key || 'dropdown' === $key || 'hierarchical' === $key || 'category' === $key || 'show_summary' === $key || 'show_author' === $key || 'show_date' === $key ) {
				$atts[ $key ] = $this->sanitizer->sanitize( 'boolval', $value );
			} else {
				$atts[ $key ] = $this->sanitizer->sanitize( 'text_field', $value );
			}
		}

		$args = array(
			'before_widget' => $atts['before_widget'],
			'after_widget'  => $atts['after_widget'],
			'before_title'  => $atts['before_title'],
			'after_title'   => $atts['after_title'],
		);

		$instance = array(
			'title'     => $atts['title'],
			'classname' => $atts['classname'],
		);

		switch ( $atts['widget'] ) {
			case 'WP_Widget_Archives':
				$instance = wp_parse_args(
					array(
						'count'    => $atts['count'],
						'dropdown' => $atts['dropdown'],
					),
					$instance
				);
				break;
			case 'WP_Widget_Categories':
				$instance = wp_parse_args(
					array(
						'count'        => $atts['count'],
						'hierarchical' => $atts['hierarchical'],
						'dropdown'     => $atts['dropdown'],
					),
					$instance
				);
				break;
			case 'WP_Widget_Custom_HTML':
				$instance = wp_parse_args(
					array(
						'content' => apply_filters( 'the_content', $content ),
					),
					$instance
				);
				break;
			case 'WP_Widget_Links':
				$instance = wp_parse_args(
					array(
						'category'    => $atts['category'],
						'description' => $atts['description'],
						'rating'      => $atts['rating'],
						'images'      => $atts['images'],
						'name'        => $atts['name'],
					),
					$instance
				);
				break;
			case 'WP_Widget_Pages':
				$instance = wp_parse_args(
					array(
						'sortby'  => $atts['sortby'],
						'exclude' => $atts['exclude'],
					),
					$instance
				);
				break;
			case 'WP_Widget_Recent_Comments':
			case 'WP_Widget_Recent_Posts':
				$instance = wp_parse_args(
					array(
						'number' => $atts['number'],
					),
					$instance
				);
				break;
			case 'WP_Widget_RSS':
				$instance = wp_parse_args(
					array(
						'url'   => $atts['url'],
						'items' => $atts['items'],

					),
					$instance
				);
				break;
			case 'WP_Widget_Tag_Cloud':
				$instance = wp_parse_args(
					array(
						'taxonomy' => $atts['taxonomy'],

					),
					$instance
				);
				break;
			case 'WP_Widget_Text':
				$instance = wp_parse_args(
					array(
						'content' => apply_filters( 'the_content', $content ),
						'filter'  => $atts['filter'],
					),
					$instance
				);
				break;
			case 'WP_Nav_Menu_Widget':
				$instance = wp_parse_args(
					array(
						'nav_menu' => $atts['nav_menu'],
					),
					$instance
				);
				break;
			default:
				break;
		}

		ob_start();
		the_widget( $atts['widget'], $instance, $args );
		$out = ob_get_clean();

		return $out;

	}

	/**
	 * TukuToi `[sidebar]` ShortCode.
	 *
	 * Displays a Sidebar.</br>
	 *
	 * Example usage:
	 * `[sidebar sidebar="my-sidebar" error="The sidebar cannot be found"]`</br>
	 * For possible attributes see the Parameters > $atts section below or use the TukuToi ShortCodes GUI.
	 *
	 * @since    2.0.0
	 * @param array  $atts {
	 *      The ShortCode Attributes.
	 *
	 *      @type string    $sidebar        The Sidebar to display. Default: ''. Accepts: valid sidebar Index, name or ID.
	 *      @type string    $error          An error if the sidebar cannot be found. Default: ''. Accepts: valid string.
	 * }
	 * @param mixed  $content   ShortCode enclosed content. Not applicable for this ShortCode.
	 * @param string $tag       The Shortcode tag. Value: 'sidebar'.
	 */
	public function sidebar( $atts, $content = null, $tag ) {

		$atts = shortcode_atts(
			array(
				'sidebar' => '',
				'error'   => 'Sidebar was not found',
			),
			$atts,
			$tag
		);

		// Sanitize the User input atts.
		foreach ( $atts as $key => $value ) {
			$atts[ $key ] = $this->sanitizer->sanitize( 'text_field', $value );
		}

		ob_start();
		$sidebar = dynamic_sidebar( $atts['sidebar'] );
		$out     = ob_get_clean();

		if ( true === $sidebar ) {
			return $out;
		} else {
			return $atts['error'];
		}
	}

}
