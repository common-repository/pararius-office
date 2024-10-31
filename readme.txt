=== Pararius Office ===
Contributors: Anno MMX
Tags: Pararius, Pararius Office
Requires at least: 3.5
Tested up to: 4.5
Stable tag: 1.2.1
License: GPLv2

Use this plugin to display properties out of the backoffice Pararius Office
on your website.

== Description ==

This plugin is an extension of the backoffice Pararius Office, which is used by
real estate brokers to manage their properties. Via this plugin, a WordPress
designer can display all the properties of that broker easily on his website.
An API key is required for it to work, so it's no use for people who are no
customer of Anno MMX. You also need to have JSON natively installed on your
webserver. Wordpress does offer a replacement for this, but it's way too slow

Check out http://www.parariusoffice.nl/website-bouwen for more documentation.

== Installation ==

1. Upload `parariusoffice/` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Start using the shortcodes in your posts/pages

== Changelog ==

= 1.0 =

Initial release

= 1.0.3 =

Use path provided by WordPress

= 1.0.4 =

Bugfixes
- Startup error solved
- cache was not always flushed

= 1.0.5 =

Custom template for maps

= 1.0.6 =

Forsale properties should have their address shown, not rentals

= 1.0.7 =

Use WordPress language for the API-calls

= 1.0.8 =

Change the way the default order is handled

= 1.0.9 =

Searching on house type performed an negative search, instead of positive

= 1.0.11 =

Fix a bug that prevented saving the forms

= 1.0.12 =

PHP 5.3 compat fix for the print functionality

= 1.0.13 =

Make searching on for sale a bit more robust

= 1.0.14 =

Typo: Descrption -> Description
Add property route on all requests

= 1.0.15 =

Change the way we handle AJAX-requests

= 1.0.16 =

Update fancybox to newest version

= 1.1.0 =

Updates to ensure we comply to the developer guides

- Use local version of jQuery smoothness theme
- Remove included `exclude-pages` plugin
- Remove fancybox dependency in favor of custom fullscreen slider

= 1.1.1 =

Defend a bit better against SEO plugins

= 1.1.2 =

Acutally use the newly found property

= 1.1.3 =

Make searching on 's-Gravenhage possible

= 1.1.4 =

PHP 5.3 compat in Api.php

= 1.1.5 =

Updated labels

= 1.1.6 =

Random properties can now (ironically) have an order
- This works with an attribute on the shortcode

= 1.2.0 =

Add functionality to have multiple api keys

- Just enter multiple api keys with a comma as separator
- Property contact requests will go to the right backoffice

Normalize city names (Den Haag <=> 's-Gravenhage)

= 1.2.1 =

Fix deleting properties
