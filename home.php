<?php
/**
 * Plugin Name:       HomePage Pop Up
 * Plugin URI:        https://example.com/homepage-popup
 * Description:       A lightweight, Elementor-compatible popup plugin that displays a custom image popup with a "Register Now" button on the homepage. Fully mobile responsive. Settings available in dashboard.
 * Version:           1.0.0
 * Author:            Sajid Khan
 * Author URI:        https://example.com
 * License:           GPL v2 or later
 * Text Domain:       homepage-popup
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class HomePagePopup {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_footer', array($this, 'display_popup'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'HomePage Pop Up',
            'HomePage Pop Up',
            'manage_options',
            'homepage-popup-settings',
            array($this, 'settings_page'),
            'dashicons-welcome-widgets-menus',
            80
        );
    }
    
    public function register_settings() {
        register_setting('homepage_popup_settings', 'hpp_image_url');
        register_setting('homepage_popup_settings', 'hpp_button_url');
        register_setting('homepage_popup_settings', 'hpp_popup_enabled');
        register_setting('homepage_popup_settings', 'hpp_delay_seconds');
    }
    
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>HomePage Pop Up Settings</h1>
            <p>Created by <strong>Sajid Khan</strong></p>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('homepage_popup_settings');
                do_settings_sections('homepage_popup_settings');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Popup</th>
                        <td>
                            <input type="checkbox" name="hpp_popup_enabled" value="1" <?php checked(get_option('hpp_popup_enabled'), 1); ?> />
                            <p class="description">Check to enable popup on homepage.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Popup Image URL</th>
                        <td>
                            <input type="url" name="hpp_image_url" value="<?php echo esc_url(get_option('hpp_image_url')); ?>" class="regular-text" placeholder="https://yourimage.jpg" />
                            <p class="description">Upload image via Media Library and paste full URL here.</p>
                            <?php if (get_option('hpp_image_url')): ?>
                                <div style="margin-top:10px;">
                                    <img src="<?php echo esc_url(get_option('hpp_image_url')); ?>" style="max-width:300px; border:1px solid #ccc;" alt="Preview">
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Register Button Link</th>
                        <td>
                            <input type="url" name="hpp_button_url" value="<?php echo esc_url(get_option('hpp_button_url')); ?>" class="regular-text" placeholder="https://yoursite.com/register" />
                            <p class="description">URL where "Register Now" button should link to.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Popup Delay (seconds)</th>
                        <td>
                            <input type="number" name="hpp_delay_seconds" value="<?php echo esc_attr(get_option('hpp_delay_seconds', 2)); ?>" min="0" max="10" />
                            <p class="description">Delay before popup appears (0 = instant).</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <hr>
            <p><strong>Tip:</strong> This plugin is optimized for Elementor. It uses minimal CSS/JS and doesn't conflict with Elementor popups.</p>
        </div>
        <?php
    }
    
    public function enqueue_assets() {
        if (!is_front_page() && !is_home()) {
            return;
        }
        
        wp_enqueue_style('hpp-style', plugin_dir_url(__FILE__) . 'assets/style.css', array(), '1.0.0');
        wp_enqueue_script('hpp-script', plugin_dir_url(__FILE__) . 'assets/script.js', array('jquery'), '1.0.0', true);
    }
    
    public function display_popup() {
        if (!is_front_page() && !is_home()) {
            return;
        }
        
        $enabled = get_option('hpp_popup_enabled');
        if (!$enabled) {
            return;
        }
        
        $image_url = get_option('hpp_image_url');
        $button_url = get_option('hpp_button_url');
        $delay = (int) get_option('hpp_delay_seconds', 2);
        
        if (empty($image_url)) {
            return;
        }
        
        ?>
        <div id="hpp-popup" class="hpp-popup-overlay" style="display: none;">
            <div class="hpp-popup-content">
                <span class="hpp-close">&times;</span>
                
                <?php if ($image_url): ?>
                    <img src="<?php echo esc_url($image_url); ?>" alt="Popup Image" class="hpp-popup-image">
                <?php endif; ?>
                
                <div class="hpp-button-wrapper">
                    <?php if ($button_url): ?>
                        <a href="<?php echo esc_url($button_url); ?>" class="hpp-register-btn">Register Now</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var delay = <?php echo $delay * 1000; ?>;
                
                setTimeout(function() {
                    if (!localStorage.getItem('hpp_popup_shown')) {
                        $('#hpp-popup').fadeIn(400);
                        localStorage.setItem('hpp_popup_shown', 'true');
                    }
                }, delay);
                
                $('.hpp-close').on('click', function() {
                    $('#hpp-popup').fadeOut(300);
                });
                
                // Close on outside click
                $('.hpp-popup-overlay').on('click', function(e) {
                    if (e.target === this) {
                        $(this).fadeOut(300);
                    }
                });
            });
        </script>
        <?php
    }
}

HomePagePopup::get_instance();

// Create assets directory and files
function hpp_create_assets() {
    $upload_dir = wp_upload_dir();
    $plugin_dir = plugin_dir_path(__FILE__);
    
    $assets_dir = $plugin_dir . 'assets';
    if (!file_exists($assets_dir)) {
        wp_mkdir_p($assets_dir);
    }
    
    // CSS File
    $css_content = '
    .hpp-popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 999999;
        padding: 20px;
        box-sizing: border-box;
    }
    
    .hpp-popup-content {
        background: #fff;
        max-width: 500px;
        width: 100%;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    
    .hpp-popup-image {
        width: 100%;
        height: auto;
        display: block;
    }
    
    .hpp-close {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 28px;
        font-weight: bold;
        color: #333;
        cursor: pointer;
        z-index: 10;
        background: rgba(255,255,255,0.8);
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }
    
    .hpp-button-wrapper {
        padding: 20px;
        text-align: center;
        background: #f8f9fa;
    }
    
    .hpp-register-btn {
        background: #007cba;
        color: #fff;
        padding: 14px 32px;
        font-size: 18px;
        font-weight: 600;
        text-decoration: none;
        border-radius: 6px;
        display: inline-block;
        transition: all 0.3s ease;
    }
    
    .hpp-register-btn:hover {
        background: #005a87;
        transform: translateY(-2px);
    }
    
    /* Mobile Responsive */
    @media (max-width: 480px) {
        .hpp-popup-content {
            max-width: 100%;
            margin: 10px;
        }
        
        .hpp-register-btn {
            padding: 12px 28px;
            font-size: 16px;
            width: 100%;
            text-align: center;
        }
    }';
    
    file_put_contents($assets_dir . '/style.css', $css_content);
    
    // JS File (empty since we inline the script)
    $js_content = '// Handled inline for better performance';
    file_put_contents($assets_dir . '/script.js', $js_content);
}
register_activation_hook(__FILE__, 'hpp_create_assets');