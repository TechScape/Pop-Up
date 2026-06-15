<?php
/**
 * Plugin Name:       HomePage Pop Up
 * Plugin URI:        https://example.com/homepage-popup
 * Description:       Displays a fully customizable popup modal on the homepage with image, CTA button, session control, and Elementor compatibility.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Sajid Khan
 * Author URI:        https://example.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       homepage-popup
 * Domain Path:       /languages
 *
 * @package HomePagePopUp
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'HPP_VERSION', '1.0.0' );
define( 'HPP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HPP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'HPP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class.
 */
final class HomePagePopUp {

	/**
	 * Single instance of the plugin.
	 *
	 * @var HomePagePopUp|null
	 */
	private static ?HomePagePopUp $instance = null;

	/**
	 * Get the single instance.
	 *
	 * @return HomePagePopUp
	 */
	public static function instance(): HomePagePopUp {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor – wire up hooks.
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load required files.
	 */
	private function load_dependencies(): void {
		require_once HPP_PLUGIN_DIR . 'includes/class-hpp-settings.php';
		require_once HPP_PLUGIN_DIR . 'includes/class-hpp-frontend.php';
	}

	/**
	 * Register hooks.
	 */
	private function init_hooks(): void {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_filter( 'plugin_action_links_' . HPP_PLUGIN_BASENAME, array( $this, 'add_settings_link' ) );

		// Init sub-components.
		HPP_Settings::instance();
		HPP_Frontend::instance();
	}

	/**
	 * Load plugin text domain.
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'homepage-popup',
			false,
			dirname( HPP_PLUGIN_BASENAME ) . '/languages'
		);
	}

	/**
	 * Add a "Settings" link on the plugins page.
	 *
	 * @param array $links Existing plugin action links.
	 * @return array
	 */
	public function add_settings_link( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=homepage-popup' ) ),
			esc_html__( 'Settings', 'homepage-popup' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}
}

/**
 * Activation hook – set default options.
 */
function hpp_activate(): void {
	$defaults = array(
		'hpp_enabled'       => '1',
		'hpp_image_id'      => '',
		'hpp_button_url'    => '',
		'hpp_button_target' => '_self',
		'hpp_delay'         => '1',
		'hpp_once_session'  => '1',
	);

	foreach ( $defaults as $key => $value ) {
		if ( false === get_option( $key ) ) {
			add_option( $key, $value );
		}
	}
}
register_activation_hook( __FILE__, 'hpp_activate' );

/**
 * Deactivation hook.
 */
function hpp_deactivate(): void {
	// Nothing to clean on deactivation; options are preserved.
}
register_deactivation_hook( __FILE__, 'hpp_deactivate' );

// Bootstrap the plugin.
HomePagePopUp::instance();
