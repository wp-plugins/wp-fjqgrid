=== WP FjqGrid ===
Tags: jquery, jqGrid, grid, table, CRUD, searchable, sortable, editable
Contributors: faina09
Donate link: http://goo.gl/QzIZZ
Requires at least: 3.5
Tested up to: 3.8
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

Info and samples at <a href="http://faina09.it/category/wp-plugins/wpfjqgrid/">WP FjqGrid developer's site</a>

== Installation ==
1. Unzip and place the 'wp-fjqgrid' folder in your 'wp-content/plugins' directory.
2. Activate the plugin.
3. Click the 'WP FjqGrid' link in the WordPress setting menu, set at least 'Enable' and one or more tables names allowed; save (step REQUIRED).
4. Use a shortcode [wp-fjqgrid table='existing_db_table' {idtable=1 caption='name to display' editable=true|false}] to display or CRUD all the contents of the table.

== Frequently Asked Questions ==
= Is it free? =
Yes! The plugin is free. And jqGrid is free too, refers to: http://www.trirand.com/blog/?page_id=932

= The plugin is not working! What can I do? =
Please send me the description of the error, and all the info you can about your configuration (WP, PHP and MySQL versions, configuration details,...). You can use WP plugin support page. I'll be happy to help you!

= What I should be aware of? =
Remember to set 'Enable' in the configuration page.
Remember to list your table in the allowed ones in configuration page.
The 'idtable' must be unique for each grid displayed: you can even display the same db table multiple times, but with differnet idtables.
Some themes may require a css tuning to display properly all the elements.

= Which are the know limitations or issues? =
will be fixed in future realises, but until now...
-. You cannot use dashes in the table name
-. Your table MUST have the first field that must be primary key
-. There are security issues. Probably should limit access to logged-in users
-. The fields render and size are set to a default, possible but not easy to set them for each field
-. No decode/pull down lists awailable
-. edit/delete/insert does not check for errors! simply refuse to work...

= Third parts js and css =
jqGrid: jQuery Grid Plugin 4.4.3 – last version which support IE6 - from http://www.trirand.com/blog/jqgrid/downloads/jquery.jqGrid-4.4.3.zip
themes: jquery-ui-themes-1.10.4.zip - from http://jqueryui.com/

== Screenshots ==
1. Setup 'WP FjqGrid'
2. Sample of 'WP FjqGrid' front page

== Changelog ==
= 0.02 =
* edit/delete/insert on popup window

= 0.01 =
* Initial release of plugin.
* Please test and report any issue you find, and any feature you want. I'll try to fix the firsts and to implement the seconds!

== Upgrade Notice ==
= 0.02 =
* edit/delete/insert on popup window
