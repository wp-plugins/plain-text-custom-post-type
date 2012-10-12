=== Plain Text Custom Post Type ===
Contributors: RyanNutt
Donate link: http://www.nutt.net/donate/
Tags: cpt, post, text, css, javascript
Requires at least: 3.0
Tested up to: 3.4
Stable tag: 0.2
License: GPLv2

Adds a custom post type for plain text files that can be used for JavaScript or CSS.

== Description ==

Create plain text files through WordPress using a custom post type.

This came about because I needed a way to edit a JavaScript file on a blog that's 
part of a multi site network setup, and I didn't want to do a child theme for 
just that one blog and keep up with updating the JS through FTP. This way I can
edit it directly inside of WordPress as I need to without firing up Notepad++ or
FileZilla.

If you select either CSS or JavaScript as the file type you'll have the option
for the plugin to automatically link to the file within your page header, assuming
your theme supports the wp_head callback. The links are created using either 
 `wp_register_script` / `wp_enqueue_script` or `wp_enqueue_style` / `wp_enqueue_style`.

And a thank you goes out to Ted Devito for his 
[Tabby jQuery plugin](http://teddevito.com/demos/textarea.html) that allows 
the capture of tabs inside the edit text area.

== Installation ==

Pretty much like any other plugin...

Either

1. Upload the `plain-text-custom-post-type` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

or

1. Download and activate through WordPress

= Usage =
Once activated, a new page will be available under the Pages tab. Click on Plain
Text Files and you'll be able to create new plain text posts. 

== Frequently Asked Questions ==

= Why isn't my file automatically linking? =

Make sure that you have selected either CSS or JavaScript as the file type for your
file. Files set as Plain Text cannot be linked in the head of your pages. 

= How did you capture the tabs? =
Okay, not really a FAQ. But I did want to make sure that Ted Divito got credit
for the [Tabby jQuery plugin](http://teddevito.com/demos/textarea.html) that I used. 
I've tried to capture tabs inside of text areas before and it was much more complicated
that I expected and my solutions never worked as well as they should. 

= Help, I need more help! =
[My site](http://www.nutt.net/tag/plain-text-cpt/) is probably your best bet. I'm 
around the WordPress forums occasionally, but my site would be faster. 

== Screenshots ==

1. Post list view of all the plain text files available
2. Publish meta box allowing you to select content type and whether to load the post automatically
3. Plain Text Files option added under the Pages tab

== Changelog ==

= 0.2 =
* Fixed a conflict between the stats module on Jetpack. 

= 0.1 =
* First release - Nothing interesting to read here...

== Upgrade Notice ==

= 0.2 =
* Fixed a conflict between the stats module in Jetpack. The JS from Jetpack was getting added to the plain text files, which it didn't need to. 

= 0.1 =
* First version, nothing to update