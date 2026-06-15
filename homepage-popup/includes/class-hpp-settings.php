<?php
/**
 * Admin settings page handler.
 *
 * @package HomePagePopUp
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HPP_Settings
 *
 * Manages the WordPress Settings API integration and admin UI.
 */
final class HPP_Settings {

	/**
	 * Single instance.
	 *
	 * @var HPP_Settings|null
	 */
	private static ?HPP_Settings $instance = null;

	/**
	 * Option group name.
	 *
	 * @var string
	 */
	private string $option_group = 'hpp_settings_group';

	/**
	 * Get the single instance.
	 *
	 * @return HPP_Settings
	 */
	public static function instance(): HPP_Settings {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Register the settings sub-menu page.
	 */
	public function add_menu_page(): void {
		add_options_page(
			esc_html__( 'HomePage Pop Up Settings', 'homepage-popup' ),
			esc_html__( 'HomePage Pop Up', 'homepage-popup' ),
			'manage_options',
			'homepage-popup',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Enqueue admin scripts (media uploader, etc.).
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_scripts( string $hook ): void {
		if ( 'settings_page_homepage-popup' !== $hook ) {
			return;
		}

		// WordPress media library.
		wp_enqueue_media();

		// Admin-specific stylesheet.
		wp_enqueue_style(
			'hpp-admin-style',
			HPP_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			HPP_VERSION
		);

		// Admin JS.
		wp_enqueue_script(
			'hpp-admin-script',
			HPP_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			HPP_VERSION,
			true
		);
	}

	/**
	 * Register all plugin settings via the Settings API.
	 */
	public function register_settings(): void {
		// Register individual options.
		$options = array(
			'hpp_enabled'       => array( $this, 'sanitize_checkbox' ),
			'hpp_image_id'      => 'absint',
			'hpp_button_url'    => 'esc_url_raw',
			'hpp_button_target' => array( $this, 'sanitize_target' ),
			'hpp_delay'         => 'absint',
			'hpp_once_session'  => array( $this, 'sanitize_checkbox' ),
		);

		foreach ( $options as $option_name => $sanitize_callback ) {
			register_setting(
				$this->option_group,
				$option_name,
				array(
					'sanitize_callback' => $sanitize_callback,
				)
			);
		}

		// ── Section: General ──────────────────────────────────────────────
		add_settings_section(
			'hpp_section_general',
			esc_html__( 'General Settings', 'homepage-popup' ),
			array( $this, 'render_section_general' ),
			'homepage-popup'
		);

		add_settings_field(
			'hpp_enabled',
			esc_html__( 'Enable Popup', 'homepage-popup' ),
			array( $this, 'render_field_enabled' ),
			'homepage-popup',
			'hpp_section_general'
		);

		add_settings_field(
			'hpp_delay',
			esc_html__( 'Popup Delay (seconds)', 'homepage-popup' ),
			array( $this, 'render_field_delay' ),
			'homepage-popup',
			'hpp_section_general'
		);

		add_settings_field(
			'hpp_once_session',
			esc_html__( 'Show Once Per Session', 'homepage-popup' ),
			array( $this, 'render_field_once_session' ),
			'homepage-popup',
			'hpp_section_general'
		);

		// ── Section: Content ──────────────────────────────────────────────
		add_settings_section(
			'hpp_section_content',
			esc_html__( 'Popup Content', 'homepage-popup' ),
			array( $this, 'render_section_content' ),
			'homepage-popup'
		);

		add_settings_field(
			'hpp_image_id',
			esc_html__( 'Popup Image', 'homepage-popup' ),
			array( $this, 'render_field_image' ),
			'homepage-popup',
			'hpp_section_content'
		);

		add_settings_field(
			'hpp_button_url',
			esc_html__( 'Button URL', 'homepage-popup' ),
			array( $this, 'render_field_button_url' ),
			'homepage-popup',
			'hpp_section_content'
		);

		add_settings_field(
			'hpp_button_target',
			esc_html__( 'Open Link In', 'homepage-popup' ),
			array( $this, 'render_field_button_target' ),
			'homepage-popup',
			'hpp_section_content'
		);
	}

	// ── Section callbacks ────────────────────────────────────────────────

	/** Render general section intro. */
	public function render_section_general(): void {
		echo '<p>' . esc_html__( 'Configure when and how often the popup appears.', 'homepage-popup' ) . '</p>';
	}

	/** Render content section intro. */
	public function render_section_content(): void {
		echo '<p>' . esc_html__( 'Set the image and call-to-action button for the popup.', 'homepage-popup' ) . '</p>';
	}

	// ── Field callbacks ──────────────────────────────────────────────────

	/** Render "Enable Popup" checkbox. */
	public function render_field_enabled(): void {
		$value = get_option( 'hpp_enabled', '1' );
		?>
		<label class="hpp-toggle">
			<input type="checkbox" name="hpp_enabled" id="hpp_enabled" value="1" <?php checked( '1', $value ); ?> />
			<span class="hpp-toggle-slider"></span>
			<span class="hpp-toggle-label"><?php esc_html_e( 'Enable popup on homepage', 'homepage-popup' ); ?></span>
		</label>
		<?php
	}

	/** Render delay field. */
	public function render_field_delay(): void {
		$value = absint( get_option( 'hpp_delay', 1 ) );
		?>
		<input type="number" id="hpp_delay" name="hpp_delay"
			   value="<?php echo esc_attr( $value ); ?>"
			   min="0" max="60" step="1" class="small-text" />
		<p class="description"><?php esc_html_e( 'Number of seconds to wait before showing the popup (0 = immediate).', 'homepage-popup' ); ?></p>
		<?php
	}

	/** Render "Show once per session" checkbox. */
	public function render_field_once_session(): void {
		$value = get_option( 'hpp_once_session', '1' );
		?>
		<label class="hpp-toggle">
			<input type="checkbox" name="hpp_once_session" id="hpp_once_session" value="1" <?php checked( '1', $value ); ?> />
			<span class="hpp-toggle-slider"></span>
			<span class="hpp-toggle-label"><?php esc_html_e( 'Show the popup only once per browser session', 'homepage-popup' ); ?></span>
		</label>
		<?php
	}

	/** Render image upload field. */
	public function render_field_image(): void {
		$image_id  = absint( get_option( 'hpp_image_id', 0 ) );
		$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
		?>
		<div class="hpp-image-field">
			<div class="hpp-image-preview" id="hpp-image-preview">
				<?php if ( $image_url ) : ?>
					<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php esc_attr_e( 'Popup Image', 'homepage-popup' ); ?>" />
				<?php endif; ?>
			</div>
			<input type="hidden" name="hpp_image_id" id="hpp_image_id" value="<?php echo esc_attr( $image_id ); ?>" />
			<button type="button" class="button button-secondary" id="hpp-upload-btn">
				<?php esc_html_e( 'Upload / Select Image', 'homepage-popup' ); ?>
			</button>
			<button type="button" class="button button-link-delete" id="hpp-remove-btn" <?php echo $image_id ? '' : 'style="display:none;"'; ?>>
				<?php esc_html_e( 'Remove Image', 'homepage-popup' ); ?>
			</button>
			<p class="description"><?php esc_html_e( 'Choose an image from the Media Library to display inside the popup.', 'homepage-popup' ); ?></p>
		</div>
		<?php
	}

	/** Render button URL field. */
	public function render_field_button_url(): void {
		$value = esc_url( get_option( 'hpp_button_url', '' ) );
		?>
		<input type="url" id="hpp_button_url" name="hpp_button_url"
			   value="<?php echo esc_attr( $value ); ?>"
			   class="regular-text" placeholder="https://example.com/register" />
		<p class="description"><?php esc_html_e( 'The URL the "Register Now" button will link to.', 'homepage-popup' ); ?></p>
		<?php
	}

	/** Render button target radio buttons. */
	public function render_field_button_target(): void {
		$value = get_option( 'hpp_button_target', '_self' );
		?>
		<fieldset>
			<label>
				<input type="radio" name="hpp_button_target" value="_self" <?php checked( '_self', $value ); ?> />
				<?php esc_html_e( 'Same Tab', 'homepage-popup' ); ?>
			</label>
			&nbsp;&nbsp;
			<label>
				<input type="radio" name="hpp_button_target" value="_blank" <?php checked( '_blank', $value ); ?> />
				<?php esc_html_e( 'New Tab', 'homepage-popup' ); ?>
			</label>
		</fieldset>
		<?php
	}

	// ── Sanitization helpers ─────────────────────────────────────────────

	/**
	 * Sanitize a checkbox (returns '1' or '').
	 *
	 * @param mixed $value Submitted value.
	 * @return string
	 */
	public function sanitize_checkbox( $value ): string {
		return ( '1' === $value ) ? '1' : '';
	}

	/**
	 * Sanitize link target.
	 *
	 * @param mixed $value Submitted value.
	 * @return string
	 */
	public function sanitize_target( $value ): string {
		return in_array( $value, array( '_self', '_blank' ), true ) ? $value : '_self';
	}

	// ── Page render ──────────────────────────────────────────────────────

	/**
	 * Render the full settings page HTML.
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'homepage-popup' ) );
		}
		?>
		<div class="wrap hpp-settings-wrap">
			<div class="hpp-admin-header">
				<div class="hpp-admin-header-inner">
					<span class="hpp-logo-icon">🎯</span>
					<div>
						<h1><?php esc_html_e( 'HomePage Pop Up', 'homepage-popup' ); ?></h1>
						<p><?php esc_html_e( 'Configure your homepage popup modal settings below.', 'homepage-popup' ); ?></p>
					</div>
				</div>
				<span class="hpp-version-badge">v<?php echo esc_html( HPP_VERSION ); ?></span>
			</div>

			<?php settings_errors( 'hpp_settings_group' ); ?>

			<div class="hpp-settings-container">
				<form method="post" action="options.php" id="hpp-settings-form">
					<?php
					settings_fields( $this->option_group );
					do_settings_sections( 'homepage-popup' );
					submit_button( esc_html__( 'Save Settings', 'homepage-popup' ), 'primary hpp-save-btn', 'submit', true );
					?>
				</form>

				<aside class="hpp-sidebar">
					<div class="hpp-sidebar-card hpp-preview-card">
						<h3>🖥️ <?php esc_html_e( 'Live Preview', 'homepage-popup' ); ?></h3>
						<p><?php esc_html_e( 'The popup will appear on your homepage after the configured delay.', 'homepage-popup' ); ?></p>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" class="button button-secondary">
							<?php esc_html_e( 'View Homepage', 'homepage-popup' ); ?>
						</a>
					</div>
					<div class="hpp-sidebar-card hpp-info-card">
						<h3>ℹ️ <?php esc_html_e( 'Quick Tips', 'homepage-popup' ); ?></h3>
						<ul>
							<li><?php esc_html_e( 'Popup only fires on the homepage (front page).', 'homepage-popup' ); ?></li>
							<li><?php esc_html_e( '"Show once per session" uses sessionStorage so repeat visitors won\'t see it again until they reopen the browser.', 'homepage-popup' ); ?></li>
							<li><?php esc_html_e( 'Works seamlessly with Elementor-built themes.', 'homepage-popup' ); ?></li>
						</ul>
					</div>
					<div class="hpp-sidebar-card hpp-credit-card">
						<p>
							<?php esc_html_e( 'Developed by', 'homepage-popup' ); ?>
							<strong>Sajid Khan</strong>
						</p>
					</div>
				</aside>
			</div>
		</div>
		<?php
	}
}
