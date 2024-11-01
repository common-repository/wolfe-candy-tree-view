=== Wolfe Candy Tree View ===
Contributors: jrackwolfe
Donate link: https://www.paypal.com/donate/?hosted_button_id=DY2RMD3J4SR3Q
Tags: tree, tree view, treeview, folder, structure, list, hierarchy, org chart, files
Requires at least: 5.0
Tested up to: 6.0.2
Stable tag: 0.1.2
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display a tree view structure in your front-end using shortcode using a range of different style templates. 

== Description ==
Display a tree view structure in your front-end using shortcode using a range of different style templates. 

*Wolfe Candy Tree View* is part of the *Wolfe Candy* suite of plugins that all work together to make [WordPress](https://wordpress.org/ "Your favorite software") site creation that much easier.

Major features include:

* The *tree view* displays any array of paths (e.g. '\a\b\c\d\', 'a\b\b', 'a\c' etc) in Windows path format (not URL or Linux) as a hierarchy based on the specified style template (or default if nothing is specified). Note: path symbols may need to be switched if inputted manually e.g. \\
* There are a number of pure CSS style templates that are uploaded with the plugin. These can all be altered to suit your design. There are also a range of other templates available.
* The hierachy list has a number of sources that can be specified from S3 structures, XML, file structures, upload folders, plugin folders, page hierarchy or a custom list.
* Up to *7* levels of hierarchy can be displayed in any one tree.
* End-nodes in the tree can hyperlink to files or other links so it can be used as a navigation tool.
* Icons and HTML Special character are supported.
* If media files are found as an end-node in a file tree, the image can be included in the tree as an image tile.
* This can be combined with the *Wolfe Candy Tool Suite* for enriched functionality e.g. S3 bucket folder lists and using *Private Content* shortcode to limit the viewing of tree structures to specified roles.

It is completely FREE - development and maintenance by the *Wolfe Candy* team headed by *Jrack Wolfe* is funded through [donations](https://www.paypal.com/donate/?hosted_button_id=DY2RMD3J4SR3Q "Donate to Wolfe Candy") from generous users.

== Frequently Asked Questions ==

= How do I make a tree view private? =

Use the tree view in conjunction with a *private content* plugin. One of these is available in the *Wolfe Candy Tool Suite* 

*Syntax: [show_content_to role="xxxxxx"] [show-tree]...... only this role can see the tree view ..... [/show_content_to]*

Use a plugin such as [WPFront User Role Editor](https://wordpress.org/plugins/wpfront-user-role-editor/ "Role Editor") to create different roles and then assign the roles using the user editor. User Editors can be a bit clunky so there is a design in the studio for a Wolfe Candy User Extension plugin so watch this space.

This can be used to make ANY content private including shortcode blocks, buttons, links, images ....

= Can I use the plug-in commercially? =

Yes, although actually monetising the plugins requires a commercial discussion with us. Please contact us at [wolfecandy@ruralcheshire.co.uk](mailto:wolfecandy@ruralcheshire.co.uk "Wolfe Candy Support").

= Why is my file and folder tree view blank? =

If using tree view with file and folder functionality you will only be able to list or show links to files and structures that the web server has sufficient permissions granted by the underlying file structure to do so. If the web server does not have access then it will not show any node that it does not have access to. If there are no permissions or an incorrect path is passed then the tree may be blank.

= How do I use the tree view to show the structure of XML documents? =

The tree view can have the source of the structure passed to it from another plugin in Windows path format. 
An XML parser plugin such as the one available in the *Wolfe Candy Tool Suite* integrates with tree view.
This has specific shortcodes to import XML and output it in tree view format.

== Screenshots ==

1. Default template
2. Basic template
3. Basic2 template
4. Boxes template
5. Cabinet template
6. Connected template
7. Elegant template
8. Elegant2 template
9. Entity template
10. Hierarchy template

== Installation ==

Upload the plugin, activate it, and then use the tools as required.

== Changelog ==

= 0.1.0 =
*Release Date - 30 September 2022*

* First release.

= 0.1.1 =
*Release Date - 6 October 2022*

* Added shortcode arg to override blank directory returns with manual input

= 0.1.2 =
*Release Date - 7 October 2022*

* Updated files to reflect that file permissions for the webserver affect the output