<?php
/**
 * Frontend popup rendering and script enqueueing.
 *
 * @package HomePagePopUp
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HPP_Frontend
 *
 * Handles enqueueing assets and rendering the popup HTML on the homepage only.
 */
final class HPP_Frontend {

	/**
	 * Single instance.
	 *
	 * @var HPP_Frontend|null
	 */
	private static ?HPP_Frontend $instance = null;

	/**
	 * Get the single instance.
	 *
	 * @return HPP_Frontend
	 */
	public static function instance(): HPP_Frontend {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_footer', array( $this, 'render_popup' ) );
	}

	/**
	 * Determine whether we should show the popup on the current page.
	 *
	 * @return bool
	 */
	private function should_show(): bool {
		// Must be enabled.
		if ( '1' !== get_option( 'hpp_enabled', '1' ) ) {
			return false;
		}

		// Only on the static front page or the blog homepage.
		if ( ! is_front_page() && ! is_home() ) {
			return false;
		}

		// Must have an image or a button URL to be useful.
		$image_id   = absint( get_option( 'hpp_image_id', 0 ) );
		$button_url = get_option( 'hpp_button_url', '' );

		if ( ! $image_id && ! $button_url ) {
			return false;
		}

		return true;
	}

	/**
	 * Enqueue frontend CSS and JS (homepage only).
	 */
	public function enqueue_assets(): void {
		if ( ! $this->should_show() ) {
			return;
		}

		wp_enqueue_style(
			'hpp-popup-style',
			HPP_PLUGIN_URL . 'assets/css/popup.css',
			array(),
			HPP_VERSION
		);

		wp_enqueue_script(
			'hpp-popup-script',
			HPP_PLUGIN_URL . 'assets/js/popup.js',
			array(),       // No jQuery dependency — vanilla JS.
			HPP_VERSION,
			true           // Load in footer.
		);

		// Pass PHP options to JS.
		wp_localize_script(
			'hpp-popup-script',
			'hppConfig',
			array(
				'delay'       => absint( get_option( 'hpp_delay', 1 ) ),
				'onceSession' => get_option( 'hpp_once_session', '1' ) === '1',
				'storageKey'  => 'hpp_shown',
			)
		);
	}

	/**
	 * Render the popup HTML in the footer (homepage only).
	 */
	public function render_popup(): void {
		if ( ! $this->should_show() ) {
			return;
		}

		$image_id     = absint( get_option( 'hpp_image_id', 0 ) );
		$button_url   = esc_url( get_option( 'hpp_button_url', '' ) );
		$button_target = get_option( 'hpp_button_target', '_self' ) === '_blank' ? '_blank' : '_self';
		$button_rel   = '_blank' === $button_target ? 'noopener noreferrer' : '';

		$image_html = '';
		if ( $image_id ) {
			$image_html = wp_get_attachment_image(
				$image_id,
				'large',
				false,
				array(
					'class'   => 'hpp-popup__image',
					'loading' => 'eager',
					'alt'     => esc_attr__( 'Popup Image', 'homepage-popup' ),
				)
			);
		}
		?>
		<!-- HomePage Pop Up – by Sajid Khan -->
		<div id="hpp-overlay" class="hpp-overlay" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Promotional Popup', 'homepage-popup' ); ?>">
			<div class="hpp-popup" id="hpp-popup">
				<button
					class="hpp-popup__close"
					id="hpp-close-btn"
					aria-label="<?php esc_attr_e( 'Close popup', 'homepage-popup' ); ?>"
					type="button"
				>&times;</button>

				<?php if ( $image_html ) : ?>
					<div class="hpp-popup__image-wrap">
						<?php if ( $button_url ) : ?>
							<a
								href="<?php echo esc_url( $button_url ); ?>"
								target="<?php echo esc_attr( $button_target ); ?>"
								<?php echo $button_rel ? 'rel="' . esc_attr( $button_rel ) . '"' : ''; ?>
								class="hpp-popup__image-link"
								aria-label="<?php esc_attr_e( 'Register Now', 'homepage-popup' ); ?>"
							>
								<?php echo $image_html; // Already escaped by wp_get_attachment_image(). ?>
							</a>
						<?php else : ?>
							<?php echo $image_html; // Already escaped by wp_get_attachment_image(). ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $button_url ) : ?>
					<div class="hpp-popup__footer">
						<a
							href="<?php echo esc_url( $button_url ); ?>"
							target="<?php echo esc_attr( $button_target ); ?>"
							<?php echo $button_rel ? 'rel="' . esc_attr( $button_rel ) . '"' : ''; ?>
							class="hpp-popup__cta"
							id="hpp-cta-btn"
						>
							<?php esc_html_e( 'Register Now', 'homepage-popup' ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
