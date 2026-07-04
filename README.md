# Bangla Date Display

Displays Bangla, Gregorian and Hijri date, Archive Calendar, time, day name, and season name in Bangla language using widgets and shortcodes.

## Plugin Info

- Link: https://wordpress.org/plugins/bangla-date-display
- Contributors: imran2w
- Developer link: https://imran.link
- Tags: Bangla, Bangla Date, Bangla Calendar, Bangla Time, Hijri Date
- Requires at least: 3.0
- Tested up to: 7.0
- Stable tag: 10.1.0
- Requires PHP: 5.6
- License: GPLv2 or later
- License URI: https://www.gnu.org/licenses/gpl-2.0.html

## Description

Displays Bangla, Gregorian and Hijri date, Archive Calendar, time, name of the day, name of the current season, and more in Bangla language. Options are also available for displaying post/page default (English) date, time, comment count, and numbers in Bangla language.

## Usage

1. Install and activate the plugin.
2. Navigate to Settings -> Bangla Date Display.
3. Use widgets or shortcodes.

## Features

- Bangla date (Bangladesh and India).
- Hijri Date.
- Gregorian date.
- Bangla day, time and current season name.
- Advanced bangla archive calendar widget.
- Options for displaying post/page's default (english) time, date, comment count etc in bangla language.

## Credits

- Developer: ALI IMRAN (https://facebook.com/imran2w)
- E-Mail: imran4dev@gmail.com
- Website: https://imran.link

## Changelog

### 10.1.0

- Added option for Hijri date roll over time.
- Ongoing improvements.

### 10.0.0

- Core features are rewritten in JS instead of PHP
- Works properly on cache-enabled sites
- Both Bangladesh and Indian calendar support
- Redesigned archive calendar widget
- Optimized for improved performance
- Fixed security issues
- Compatible with WP 7.0

### 1.0 - 9.4

- Many changes and improvements

## Installation

### Method 1

1. Navigate to Plugins -> Add New -> Search.
2. Search for Bangla Date Display.
3. Install and activate plugin.
4. Go to Settings -> Bangla Date Display.

### Method 2

1. Download plugin zip file.
2. Upload and install.
3. Activate plugin.
4. Go to Settings -> Bangla Date Display.

## Frequently Asked Questions

### How does it work?

Use these shortcodes in posts/pages:

- [bangla_date]
- [gregorian_date]
- [hijri_date]
- [bangla_day]
- [bangla_time]
- [bangla_season]
- [ajax_calendar start_year="2026" language="bn"]

Or use these PHP snippets in theme templates:

```php
<?php echo do_shortcode('[bangla_date]'); ?>
<?php echo do_shortcode('[gregorian_date]'); ?>
<?php echo do_shortcode('[hijri_date]'); ?>
<?php echo do_shortcode('[bangla_day]'); ?>
<?php echo do_shortcode('[bangla_time]'); ?>
<?php echo do_shortcode('[bangla_season]'); ?>
<?php echo do_shortcode('[ajax_calendar start_year="2026" language="bn"]'); ?>
```

### Is it customizable?

Yes. After installation and activation, go to Settings -> Bangla Date Display.

## Screenshots

1. Main Widget
2. Time and Date
3. Advanced Archive Calendar Widget
4. Settings Page
