=== Pavel Silinskii Popup Builder ===
Contributors: pavelsilinskii
Tags: popup, modal, popup builder, newsletter popup, exit intent
Requires at least: 5.9
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight popup builder for WordPress. Create popups for promotions, newsletter signups, and announcements.

== Description ==

Pavel Silinskii Popup Builder lets you create beautiful popups without touching any code.

**Features:**

* Create unlimited popups using the WordPress editor
* 4 trigger types: delay, scroll percentage, exit intent, click on element
* Display frequency: once per visitor, once per session, or always
* Display rules: all pages, front page only, or specific pages
* 3 animations: fade, slide up, zoom
* Customizable colors, width, overlay
* Impression, dismissal and click tracking
* Lightweight — no jQuery, no page speed impact
* GDPR-friendly — no external requests

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/pavel-silinskii-popup-builder` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to Settings → Popup Builder to create your first popup.

== Frequently Asked Questions ==

= Does this plugin slow down my site? =

No. Scripts and styles are loaded only on pages where at least one active popup is set to display.

= Is it GDPR compliant? =

The plugin makes no external requests and sets no cookies. To count impressions it stores a salted hash of the visitor's IP address and user agent; the raw IP address is never stored. Display frequency is remembered in the visitor's browser (localStorage / sessionStorage).

= How do I open a popup when a button is clicked? =

Set the trigger to "On Click" and enter a CSS selector such as `.open-popup` or `#promo-link`. Add that class or ID to any button or link on your site.

== Changelog ==

= 1.0.0 =
* Initial release
