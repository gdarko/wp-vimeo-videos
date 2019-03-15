=== WP Vimeo Videos ===
Contributors: DarkoG
Tags: vimeo, videos, upload, embed video, embed, embed vimeo
Requires at least: 3.5
Stable Tag: 1.0.0
Requires PHP: 5.5.0
Tested up to: 5.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Upload vimeo videos and embed the uploaded videos directly from WordPress

== Description ==

Simple plugin that integrates your WordPress site with vimeo. Lets you upload vimeo videos directly through WordPress and embed to your site using  shortcode.

== Installation ==

= Plugin Installation =

* Download the plugin from the WordPress.org repository
* Go to your WordPress Dashboard, navigate to Plugins > Add Plugin and upload the zip file you downloaded.
* Assuming you installed the plugin successfully, you can continue using the plugin by navigating to Media > Vimeo

= Plugin Configuration =

* Go to <a target="_blank" href="https://developer.vimeo.com/">Vimeo Developer Portal</a> sign up and "Create App"
* Navigate to My Apps in developer portal, click the app you created
* You need to obtain the following keys and save them in the "Settings" page:
* Client ID: Copy the code from "Client Identifier"
* Client Secret: Copy the code that is shown in the "Client Secrets" area
* Access Token: Click "Generate an access token", select "Authenticated" and select the following scopes: "Public, Private, Edit, Upload, Delete, Create, Interact, Video Files"
* Done, make sure you saved those in Vimeo settings page and try to upload your first video.

= If you have any question feel free to get in touch =

== Frequently Asked Questions ==

= Can i use it without Client ID, Client Secret and Access Token? =

No, you must have Client ID, Client Secret and Access Token with the required permissions.

= Will you support PHP5+ ? =

No, the official vimeo PHP library requires PHP 7.1.0. I tried to adopt the older vimeo library that supported PHP 5.5.0 and onwards but it was too buggy so i decided to bump the minimal PHP requirement to PHP 7.1.0. If you want to use the plugin please make sure you upgrade your PHP version or contact your host.

== Screenshots ==

1. Vimeo Uploads
2. Vimeo Settings
3. Example Vimeo Developer APP and how to get the required access keys.

== Changelog ==

= Version 1.0.0 =
* Initial version