=== HomePage Pop Up ===
Contributors:      sajidkhan
Tags:              popup, modal, homepage, cta, elementor
Requires at least: 5.8
Tested up to:      6.7
Requires PHP:      7.4
Stable tag:        1.0.0
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Displays a fully-customizable popup modal on the homepage with an image, "Register Now" CTA button, configurable delay, and session control.

== Description ==

**HomePage Pop Up** is a lightweight, production-ready WordPress plugin that shows a polished modal popup exclusively on your homepage. It is built with WordPress best practices and is fully compatible with Elementor-based themes.

= Key Features =

* ✅ Show popup **on the homepage only** — zero impact on other pages
* 🖼️ Upload a custom **popup image** directly from the WordPress Media Library
* 👉 **"Register Now"** CTA button with configurable URL and tab target (same tab / new tab)
* ⏱️ **Configurable delay** (0–60 seconds) before the popup appears
* 🍪 **Show once per session** using `sessionStorage` — no cookies, no GDPR hassle
* 🔴 **Enable / Disable** toggle — instantly pause the popup without deactivating the plugin
* 📱 Fully **mobile responsive** — bottom-sheet style on small screens
* ⚡ **Vanilla JS** — no jQuery, no heavy libraries on the frontend
* 🎨 **Modern UI** — glassmorphism overlay, smooth fade + scale animation, gradient CTA button
* ♿ **Accessible** — ARIA roles, focus trapping, Escape key to close
* 🧩 **Elementor compatible** — no conflicts with Elementor popups or widgets

= How It Works =

1. Install and activate the plugin.
2. Go to **Settings → HomePage Pop Up**.
3. Upload your promotional image, set the CTA link, configure delay, and hit **Save Settings**.
4. Visit your homepage — the popup will appear after the configured delay!

== Installation ==

1. Upload the `homepage-popup` folder to `/wp-content/plugins/`.
2. Activate the plugin from **Plugins → Installed Plugins** in the WordPress dashboard.
3. Go to **Settings → HomePage Pop Up** to configure the popup.

== Frequently Asked Questions ==

= Does this work with Elementor? =
Yes. The plugin is fully compatible with Elementor and Elementor Pro. It doesn't inject any styles that conflict with Elementor's layouts or popups.

= Does the popup show on every page? =
No. The plugin checks for `is_front_page()` and `is_home()` so it only fires on the homepage (whether it's a static page or the blog index).

= What does "Show Once Per Session" mean? =
When enabled, the popup is recorded in the browser's `sessionStorage`. The visitor will not see the popup again until they close and reopen their browser (starting a new session).

= What if I don't set an image or a button URL? =
The popup will not render at all if neither an image nor a button URL has been configured. This prevents an empty popup from appearing.

= Is this GDPR-friendly? =
Yes. The plugin uses `sessionStorage` (not persistent cookies), so no data is persisted beyond the current browser session.

== Screenshots ==

1. **Admin Settings Page** — clean two-column layout with toggle switches and image uploader.
2. **Frontend Popup (Desktop)** — centered modal with image and "Register Now" CTA button.
3. **Frontend Popup (Mobile)** — bottom-sheet design with full-width CTA button.

== Changelog ==

= 1.0.0 =
* Initial release.
* Admin settings page with Settings API integration.
* Media Library image upload support.
* Configurable delay, session control, and tab target.
* Responsive modal with modern UI/UX.
* Elementor compatibility verified.

== Upgrade Notice ==

= 1.0.0 =
Initial release — no upgrade steps required.

== Developer Notes ==

Developed by **Sajid Khan**.

The plugin follows WordPress coding standards throughout:
- All user inputs are sanitized with `absint()`, `esc_url_raw()`, and custom sanitization callbacks.
- All outputs are escaped with `esc_html()`, `esc_url()`, `esc_attr()`, and `wp_get_attachment_image()`.
- Scripts are enqueued with proper version strings and deferred to the footer.
- Assets are only loaded on the homepage to keep all other pages unaffected.
