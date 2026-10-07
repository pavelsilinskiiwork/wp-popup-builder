# Pavel Silinskii Popup Builder

Lightweight popup builder for WordPress. Create popups for promotions, newsletter signups, and announcements with flexible trigger and display rules.

![WordPress](https://img.shields.io/badge/WordPress-5.9%2B-21759b?logo=wordpress)
![Tested up to](https://img.shields.io/badge/tested%20up%20to-7.1-21759b)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-777bb4?logo=php&logoColor=white)
![License](https://img.shields.io/badge/license-GPLv2%20or%20later-blue)

## Features

- **Popups built in the WordPress editor**: text, images, buttons, shortcodes, and newsletter forms
- **4 triggers**: after a delay, at a scroll percentage, on exit intent, or on click of any element
- **Display frequency**: once per visitor, once per session, or every visit
- **Page rules**: all pages, front page only, or a list of specific page/post IDs
- **Appearance**: width, background, text, and button colors, overlay, close button, plus fade, slide-up, and zoom animations
- **Built-in stats**: counts impressions, dismissals, and clicks inside the popup
- **Lightweight**: vanilla JS on the frontend (no jQuery). Assets load only on pages where a popup will display
- **Privacy-friendly**: no external requests, no cookies, and raw IP addresses are never stored
- **Accessible**: `role="dialog"` with focus management, Escape to close, and support for `prefers-reduced-motion`

## Requirements

| | Minimum |
|---|---|
| WordPress | 5.9 |
| PHP | 8.0 |

## Installation

**From a release ZIP**

1. Download the latest ZIP from [Releases](https://github.com/pavelsilinskiiwork/pavel-silinskii-popup-builder/releases).
2. In WordPress, go to **Plugins → Add New → Upload Plugin** and upload the ZIP.
3. Activate **Pavel Silinskii Popup Builder**.

**From source**

```bash
cd wp-content/plugins
git clone https://github.com/pavelsilinskiiwork/pavel-silinskii-popup-builder.git
```

Then activate the plugin on the **Plugins** screen.

## Usage

1. Go to **Settings → Popup Builder** and click **Add New Popup**.
2. Write the popup content in the editor. The title is used as the dialog's accessible label.
3. Configure the meta boxes:

| Box | Setting | Options |
|---|---|---|
| Trigger Settings | Trigger type | On Delay · On Scroll · Exit Intent · On Click |
| | Delay | Seconds before showing (delay trigger) |
| | Scroll percentage | 0–100 (scroll trigger) |
| | Click selector | CSS selector, e.g. `.open-popup` (click trigger) |
| Display Rules | Frequency | Once per visitor · Once per session · Every visit |
| | Pages | All pages · Front page only · Specific pages (comma-separated IDs) |
| Appearance | Width | Pixels (capped at 90% of the viewport) |
| | Colors | Background, text, button |
| | Animation | Fade · Slide up · Zoom |
| | Overlay / close options | Show overlay, close on overlay click, show close button |
| Status | Enabled | Turns the popup on or off without unpublishing it |

4. Click **Publish**. Only published **and** enabled popups appear on the site.

### Opening a popup from a button

Set the trigger to **On Click** and the selector to `.open-popup`, then add that class to any link or button:

```html
<a href="#" class="open-popup">Get 10% off</a>
```

The click is intercepted, so the link doesn't navigate. The frequency setting still applies: with "Once per visitor", later clicks do nothing.

### Closing a popup from your own markup

Any element with `data-pspb-dismiss="{popup ID}"` closes that popup. You can also call the global function:

```js
window.pspbDismissPopup(123);
```

## How it works

### Frequency

Frequency is tracked in the visitor's browser under the key `pspb_shown_{popup ID}`:

- **Once per visitor**: `localStorage`
- **Once per session**: `sessionStorage`
- **Every visit**: not stored

If browser storage is blocked, the popup is shown rather than suppressed.

### Statistics

The frontend sends an AJAX request (`admin-ajax.php`, action `pspb_dismiss`, nonce-protected) for each event. Every event is stored as a row in `{prefix}pspb_impressions`:

| Status | Recorded when |
|---|---|
| `shown` | The popup opens |
| `dismissed` | The visitor closes it (close button, overlay, Escape) |
| `converted` | The visitor clicks a link or button inside the popup |

The **Impressions** column on the list page and in the editor's Status box counts `shown` events.

### Data stored

- **Post type** `pspb_popup`: popup content and title.
- **Post meta** `_pspb_*`: one key per setting.
- **Option** `pspb_version`: the installed version, used to run database upgrades.
- **Table** `{prefix}pspb_impressions`: `popup_id`, `visitor_key`, `status`, `created_at`.

`visitor_key` is a salted hash (`wp_hash()`) of the visitor's IP address and user agent. The raw IP address is never written to the database.

### Uninstall

Deleting the plugin from the **Plugins** screen removes all popups and their meta, drops the impressions table, and deletes the `pspb_version` option.

## Notes and limitations

- Up to 10 enabled popups are loaded per page.
- Exit intent relies on mouse movement, so it doesn't fire on touch devices.
- The plugin uses the classic editor for popups so that the settings meta boxes sit next to the content.
- If you use full-page caching, the AJAX nonce in cached pages expires after 12–24 hours. Popups keep working, but stats stop being recorded until the cache is refreshed. Set the cache lifetime below 12 hours if statistics matter to you.

## Project structure

```
pavel-silinskii-popup-builder/
├── pavel-silinskii-popup-builder.php   # Bootstrap: constants, includes, hooks
├── uninstall.php                       # Removes all plugin data
├── readme.txt                          # WordPress.org readme
├── includes/
│   ├── class-pspb-installer.php        # Creates the impressions table
│   ├── class-pspb-post-type.php        # pspb_popup post type, settings and defaults
│   ├── class-pspb-frontend.php         # Page rules, asset loading, rendering
│   └── class-pspb-ajax.php             # Event tracking and impression counts
├── admin/
│   ├── class-pspb-admin.php            # Settings → Popup Builder list page
│   └── class-pspb-meta-boxes.php       # Editor meta boxes and saving
├── templates/
│   └── popup.php                       # Popup and overlay markup
├── assets/
│   ├── css/  (pspb-frontend.css, pspb-admin.css)
│   └── js/   (pspb-frontend.js, pspb-admin.js)
└── languages/
    └── pavel-silinskii-popup-builder.pot
```

All PHP code uses the `PavelSilinskii\PopupBuilder` namespace. Hooks, options, and meta keys use the `pspb_` prefix.

## Translations

The text domain is `pavel-silinskii-popup-builder`. A template is included at [`languages/pavel-silinskii-popup-builder.pot`](languages/pavel-silinskii-popup-builder.pot). To regenerate it with WP-CLI:

```bash
wp i18n make-pot . languages/pavel-silinskii-popup-builder.pot
```

## Changelog

### 1.0.0
- Initial release.

## License

[GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html) © Pavel Silinskii
