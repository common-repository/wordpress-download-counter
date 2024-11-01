=== WordPress Download Counter ===
Contributors: Viper007Bond
Donate link: http://www.viper007bond.com/donate/
Tags: wordpress, downloads, widget
Requires at least: 2.8
Tested up to: 2.8
Stable tag: trunk

Allows you to show the download counter for WordPress on your site.

== Description ==

Now you too can show the [WordPress download counter](http://wordpress.org/download/counter/) on your website! This plugin adds a widget to your blog that shows the current download count and even refreshes the count every 15 seconds automatically.

**Requirements**

* PHP 5.2.0 or newer (PHP4 is dead anyway)
* WordPress 2.8 or newer

== Installation ==

###Manual Installation###

Extract all files from the ZIP file, **making sure to keep the file/folder structure intact**, and then upload it to `/wp-content/plugins/`.

###Automated Installation###

Visit Plugins -> Add New in your admin area and search for this plugin. Click "Install".

**See Also:** ["Installing Plugins" article on the WP Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)

###Plugin Usage###

Visit Appearance -> Widgets and drag the new widget into your sidebar.

== Frequently Asked Questions ==

= It's not working! =

Are you running at least PHP 5.2.0 and WordPress 2.8? You need to be.

== Screenshots ==

1. Example widgets (that's two instances there, the lower one has a custom title) in the default Kubrick theme.

== ChangeLog ==

**Version 1.0.2**

* Send AJAX requests to `wp-load.php` to minimize code that's loaded (queries, etc.).

**Version 1.0.1**

* Send nocache headers for the AJAX to make sure it doesn't cache.

**Version 1.0.0**

* Initial release!