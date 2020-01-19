=== WP Vimeo Videos ===
Contributors: DarkoG
Tags: vimeo, videos, upload, embed video, embed, embed vimeo
Requires at least: 4.2
Stable Tag: 1.2.0
Requires PHP: 5.5.0
Tested up to: 5.2.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Embed and upload videos to Vimeo directly from WordPress

== Description ==

**Integrtes your WordPress site with Vimeo using the Vimeo API and allows the user to upload videos directly from WordPress. :)**

**Disclaimer:** I don't work for Vimeo and the plugin is not official Vimeo software. It just uses their Developer API to provide interface for uploading videos directly from WordPress.

Especially useful in the following cases:
- If you want to speed up the entire process. No need to login to Vimeo, you can now upload videos directly WordPress.
- If you don't want to share your Vimeo login credentials especially when running multi-author blog.

=== How it works ===

In order to be able to connect to Vimeo you will need to sign up on <a target="_blank" href="https://developer.vimeo.com/">Vimeo Developer Portal</a> and request access to the Vimeo API. Please check the Installation tab and also the screenshot 4.

<a href="https://bit.ly/wvvdocs" target="_blank">Detailed Guide</a>

=== Features  ===

* Upload videos from the Media screen
* Upload videos from the Gutenberg editor
* Responsive embeds from the Gutenberg editor
* Media > Vimeo is accessible by the users that have the capability upload_files (Author, Editor, Administrators by default)
* Settings > Vimeo is accessible by the users that have the capability manage_options (Administrators by default)
* Shortcode available [dgv_vimeo_video id="the_vimeo_id"]
* Useful API information and tips in the Settings > Vimeo Page

=== Premium Version ===

Additional features as follows:

 * Gutenberg Support
 * Classic Editor Support (TinyMCE)
 * Front-end upload via Gravtiy Forms
 * Option to upload existing Media Library videos to Vimeo in WordPress with one click
 * Option to search your Vimeo account for existing videos when embedding video
 * Whitelist domains for embedding, allow embed on specific domains only for newly uploaded videos
 * Update Vimeo Videos from your WordPress site
 * Delete Vimeo Videos from your WordPress site
 * Fast Dedicated Support for premium customers

 <a href="http://bit.ly/wvvpurchase" target="_blank">Get Premium Version</a>

== Installation ==

= Plugin Installation =

* Download the plugin from the WordPress.org repository
* Go to your WordPress Dashboard, navigate to Plugins > Add Plugin and upload the zip file you downloaded.
* Setup your pereferences and API credentials from Settings > Vimeo
* Upload videos from Media > Vimeo or the editor

= Plugin Configuration =

* Go to <a target="_blank" href="https://developer.vimeo.com/">Vimeo Developer Portal</a> sign up and "Create App"
* Navigate to My Apps in developer portal, click the app you created
* You need to obtain the following keys and save them in the "Settings > Vimeo" page:
* Client ID: Copy the code from "Client Identifier"
* Client Secret: Copy the code that is shown in the "Client Secrets" area
* Access Token: Click "Generate an access token", select "Authenticated" and select the following scopes: "Public, Private, Edit, Upload, Delete, Create, Interact, Video Files"
* Done, make sure you saved those in Vimeo settings page and try to upload your first video.

= If you have any question feel free to get in touch =

== Frequently Asked Questions ==

= Can i use it without Client ID, Client Secret or Access Token? =

No, you must have Client ID, Client Secret and Access Token with the required scopes/permissions.

= Which API scopes are required =

Most of them. Especially if you are using the premium version. So it's best to select the following at least: public, private, create, edit, delete, upload, video_files, interact

= Do i need to do any tweaks to the hosting configuration for bigger files ? =

The files are streamed directly from your browser to Vimeo using the TUS protocol, so the upload process doesn't rely on the server where your site is hosted anymore. Therefore no need to adjust any settings.

== Screenshots ==

1. Main upload form
2. Gutenberg upload block
3. Settings screen that showing useful API information
4. Example Vimeo Developer APP and how to get the required access keys.

== Changelog ==

= Version 1.2.0 =
* Added tinyMCE/Classic Editor support (PRO)
* Added front-End upload support for GravityForms via field (PRO)
* Added Vimeo Video Delete Option (PRO)
* Added Vimeo Video Privacy Management (PRO)
* Added option to upload existing media library videos to Vimeo (PRO)
* Added option to search your Vimeo account when embedding video (PRO)
* Added direct link in the admin Vimeo list table
* Added instructions/welcome screen
* Added option to enable/disable view access in the list table if the user didn't uploaded the video
* Added author filter on the videos list table
* Added author column on the videos list table
* Added performance tweaks
* Added UI improvements in dashboard screens
* Added useful information in the Settings screen
* Updated examples

= Version 1.1.2 =
* Fix Fatal Error in the Settings page (news section)

= Version 1.1.1 =
* Fix Settings form saving

= Version 1.1.0 =
* Major rewrite. Includes lots of code improvements and paves the way for more development in future.
* Added API Information section in the settings page. You can now see if you are connected, which scopes you have assigned and much more.
* Added Gutenberg block. It's now possible to upload video or choose from existing directly from the editor.
* Added support for direct upload approach that doesn't require the files to stay on the server for some time. You can easily switch through Settings now.
* Moved Settings page to Settings menu in the Dashboard
* Improved Media > Vimeo permissions. The page is accessible for Authors, Editors and Administrators or other users that have the capability upload_files
* Improved Settings > Vimeo permissions. The page is accessible only by Administrators or other users that have the capability manage_options
* Fixed cron clean up task problem for the pull upload approach

= Version 1.0.3 =
* Fix fatal error in some cases.

= Version 1.0.2 =
* Fix problem with file names that Vimeo didn't accepted.
* Added cron event to automatically clean up the videos that are already processed.

= Version 1.0.1 =
* Security improvmenets

= Version 1.0.0 =
* Initial version