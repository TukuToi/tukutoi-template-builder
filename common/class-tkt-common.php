<?php
/**
 * The TukuToi Common functionality handler.
 *
 * Orchestrates all commonly used functionality of all the TukuToi plugins.
 * Maintains Menu items, shared logic, scripts and more.
 *
 * @link       https://www.tukutoi.com/
 * @since      1.0.0
 *
 * @package    Tkt_Common
 */

/**
 * The TukuToi Common functionality.
 *
 * Define Common Constants, menus, version and scripts.
 *
 * @package    Tkt_Common
 * @author     TukuToi <hello@tukutoi.com>
 */
class Tkt_Common {

	/**
	 * The TukuToi Actions Common to all TukuToi plugins
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $common_actions    All actions used by TukuToi Plugins in common.
	 */
	protected $common_actions;

	/**
	 * The TukuToi Common Version
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $common_version    Defines version of common code loaded.
	 */
	protected $common_version;

	/**
	 * The TukuToi Common Name
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $common_name    Defines Name of TukuToi Common loaded.
	 */
	protected $common_name;

	/**
	 * The Vendor Name
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $vendor_name    Defines Vendor of the Company.
	 */
	protected $vendor_name;

	/**
	 * Load only one instance of this class
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      bool    $instance    Is class instantiated.
	 */
	private static $instance = null;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	protected function __construct() {

		$this->vendor_name      = 'TukuToi';
		$this->common_name      = 'tkt_common';
		$this->common_version   = '1.0.0';
		$this->common_actions   = array();
		$this->define_loaded();

	}

	/**
	 * Flag Common Code as loaded
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_loaded() {
		if ( ! defined( 'TKT_COMMON_LOADED' ) ) {
			define( 'TKT_COMMON_LOADED', true );
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'common/class-tkt-shortcodes-processor.php';
		}
	}

	/**
	 * Load Common Code
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function load() {
		$this->common_actions();
		foreach ( $this->common_actions as $action ) {
			$this->$action();
		}
	}

	/**
	 * Get Common Name
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function get_common_name() {
		return $this->common_name;
	}

	/**
	 * Instantiate class
	 *
	 * @since    1.0.0
	 * @access   public
	 * @paramt      $instance    Is class instantiated.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new TKT_Common();
		}
		return self::$instance;
	}

}
