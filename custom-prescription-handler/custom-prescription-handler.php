<?php
/**
 * Plugin Name:       Custom Prescription Handler for WooCommerce
 * Plugin URI:        https://example.com/plugins/custom-prescription-handler/
 * Description:       Adds custom prescription and lens selection options to WooCommerce products.
 * Version:           1.0.0
 * Author:            Jules (AI Assistant)
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       custom-prescription-handler
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'CPH_VERSION', '1.0.0' );
define( 'CPH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CPH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The core plugin class
 */
final class Custom_Prescription_Handler {

	/**
	 * The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Main Instance.
	 * Ensures only one instance of the plugin class is loaded or can be loaded.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->setup_hooks();
		$this->includes();
		$this->init_classes();
	}

	/**
	 * Instantiate classes.
	 */
	private function init_classes() {
		new CPH_Admin_Product_Settings();
	}

	/**
	 * Setup essential plugin hooks.
	 */
	private function setup_hooks() {
		// Activation and deactivation hooks
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Enqueue scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
	}

	/**
	 * Include required files.
	 */
	private function includes() {
		require_once CPH_PLUGIN_DIR . 'includes/admin/class-cph-admin-product-settings.php';
		require_once CPH_PLUGIN_DIR . 'includes/frontend/class-cph-frontend-display.php';
	}

	/**
	 * Plugin activation callback.
	 */
	public function activate() {
		// Flush rewrite rules if custom post types or taxonomies were registered.
		// Not strictly needed for this plugin's current scope but good practice.
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation callback.
	 */
	public function deactivate() {
		// Code to run on plugin deactivation, if any.
		flush_rewrite_rules();
	}

	/**
	 * Enqueue admin scripts and styles.
	 */
	public function enqueue_admin_scripts( $hook_suffix ) {
		// Only load on specific admin pages, e.g., product edit page
		// if ( 'post.php' === $hook_suffix || 'post-new.php' === $hook_suffix ) {
		//  global $post;
		//  if ( $post && 'product' === $post->post_type ) {
		//      wp_enqueue_style( 'cph-admin-style', CPH_PLUGIN_URL . 'assets/css/admin-style.css', array(), CPH_VERSION );
		//      wp_enqueue_script( 'cph-admin-script', CPH_PLUGIN_URL . 'assets/js/admin-script.js', array( 'jquery' ), CPH_VERSION, true );
		//  }
		// }
		// For now, let's assume a general admin style for broader use if needed.
		wp_enqueue_style( 'cph-admin-style', CPH_PLUGIN_URL . 'assets/css/admin-style.css', array(), CPH_VERSION );
	}

	/**
	 * Enqueue frontend scripts and styles.
	 */
	public function enqueue_frontend_scripts() {
		// Only load on single product pages
		if ( is_product() ) {
			wp_enqueue_style( 'cph-frontend-style', CPH_PLUGIN_URL . 'assets/css/frontend-style.css', array(), CPH_VERSION );
			wp_enqueue_script( 'cph-frontend-script', CPH_PLUGIN_URL . 'assets/js/frontend-script.js', array( 'jquery' ), CPH_VERSION, true );

			// Pass data to frontend script if needed (e.g., AJAX URL, nonces)
			// wp_localize_script('cph-frontend-script', 'cph_params', array(
			// 'ajax_url' => admin_url('admin-ajax.php'),
			// 'nonce'    => wp_create_nonce('cph_nonce')
			// ));
		}
	}
}

/**
 * Begins execution of the plugin.
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
function cph_run_plugin() {
	return Custom_Prescription_Handler::instance();
}
cph_run_plugin();

?>
