=== WPF-jqGrid ===
Tags: jquery, jqGrid, grid, table, CRUD, searchable, sortable, editable
Contributors: faina09
Donate link: http://goo.gl/QzIZZ
Requires at least: 3.5
Tested up to: 4.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Use jqGrid (jquery plugin) to manage database tables.

== Description ==
Use the jqGrid plugin of jquery to manage MySql db tables.

Works with PHP 5.2.12 or higher: please report any issue you find, and any feature you want. I'll try to fix the firsts and to implement the seconds!

This is a very first release with many limitations; jqGrid offers a lot of features but only few are actually supported by this plugin.

I planned to develop many other features, but this depends by the interest of the users, and eventually from the support. If you want to contribute with a translation or writing code ask me, if you want to make a donation here's the link.

If any bug found please ask me for support!

Info and samples at <a href="http://faina09.it/category/wp-plugins/wpfjqgrid/">WPF-jqGrid developer's site</a>

== Installation ==
1. Unzip and place the 'wpf-jqgrid' folder in your 'wp-content/plugins' directory.
2. Activate the plugin.
3. Click the 'WPF-jqGrid' link in the WordPress setting menu
4. Check the 'Enable' and one or more tables names allowed (the precompiled fields are intended for a fast start: just click "save")
5. Save (step REQUIRED).
6. Use a shortcode like [wpf-jqgrid table='wpf_jqgrid_sample' idtable=1 caption='name to display' editable=true] in any page or post to display a CRUD for the table!

== Frequently Asked Questions ==
= Is it free? =
Yes! The plugin is free. And jqGrid is free too, refers to: http://www.trirand.com/blog/?page_id=932

= The plugin is not working! What can I do? =
Please send me the description of the error, and all the info you can about your configuration (WP, PHP and MySQL versions, configuration details,...).
Useful may be also the MySQL script to create your table and the malfunctioning page html code.
You can use WP plugin support page. I'll be happy to help you!

= What I should be aware of? =
* Remember to set 'Enable' in the configuration page.
* Remember to list your table in the allowed ones in configuration page.
* The 'custom fields formatting' is very tricky: use it only if you know what you are doing!
* The 'idtable' must be unique for each grid displayed: you can even display the same db table multiple times, but with differnet idtables.
* Some themes may require a css tuning to display properly all the elements.

= Which are the know limitations or issues? =
* Your table MUST have ONE and ONLY ONE field set as primary key, if not the first field will be use as PK.
* The fields render and size are set to a default, possible but not easy to set them for each field
* No decode/pull down lists available
* Must insert numbers with DOT decimal separator and NO thousand separator
* Datetime edit check is not supported in jqGrid
* (all these will be fixed in future releases, but until now...)

= Third parts js and css =
* jqGrid: jQuery Grid Plugin 4.4.3 – last version which support IE6 - from http://www.trirand.com/blog/jqgrid/downloads/jquery.jqGrid-4.4.3.zip
* themes: jquery-ui-themes-1.10.4.zip - from http://jqueryui.com/

= TODO =
* set rights to modify tables on setup and check on frontend
* set key field(s) on fronted or from DB
* decode required fields with a scrolldown list
* edit/delete/insert on line
* 1 to n slave table
* simplify formatting settings on frontend for each field of a selected table

== Screenshots ==
1. Setup 'WPF-jqGrid'
2. Sample of 'WPF-jqGrid' front page, see live at <a href="http://faina09.it/category/wp-plugins/wpfjqgrid/">WPF-jqGrid developer's site</a>
3. Edit popup window, see live at <a href="http://faina09.it/category/wp-plugins/wpfjqgrid/">WPF-jqGrid developer's site</a>

== Changelog ==
= 0.12 =
* WP 4.0
= 0.11 =
* deprecated mysql_ calls removed, use mysqli
* wp 3.9 compatible
= 0.10 =
* sortby=field,asc|desc into shortcode
* ATTENTION: renamed, need resave setup page and rename shortcode to [wpf-jqgrid]
= 0.09 =
* admin-ajax.php path fix (tnx to michael walker)
= 0.08 =
* js fix
* reformat code
= 0.07 =
* minor fix (quotes, empty vars,..)
* better log for debug
= 0.06 =
* any field (but only one) can be PK 
* add log with settable level
* add role (converted to capability) required to edit a table
* some formatting added
= 0.05 =
* fast and simplified first run
* fix key field name and usage
* edit date format
= 0.04 =
* create tables from backend
* fast startup parameters on setup page
= 0.03 =
* fix - better readme
= 0.02 =
* edit/delete/insert on popup window
= 0.01 =
* Initial release of plugin.
* Please test and report any issue you find, and any feature you want. I'll try to fix the firsts and to implement the seconds!

== Upgrade Notice ==
= 0.09 =
* admin-ajax.php path fix (tnx to michael walker)
= 0.05 =
* fast and simplified first run
= 0.02 =
* edit/delete/insert on popup window
