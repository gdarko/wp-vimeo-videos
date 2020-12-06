=== WP Vimeo Videos ===
Contributors: DarkoG
Tags: vimeo, videos, upload, embed video, embed, embed vimeo
Requires at least: 4.2
Stable Tag: 1.5.4
Requires PHP: 5.5.0
Tested up to: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Embed and upload videos to Vimeo directly from WordPress

== Description ==

**Integrtes your WordPress site with Vimeo using the Vimeo API and allows the user to upload videos directly from WordPress. 8-)**

**Disclaimer:** I don't work for Vimeo and the plugin is not official Vimeo software. It just uses Vimeo Developer API to provide interface for uploading videos directly from WordPress.

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
* Vimeo connection problem detection mechanism in Settings page

=== Premium Version ===

Additional features as follows:

* Front-end upload via Gravtiy Forms
* Gutenberg Support with additional options (eg. account search)
* Classic Editor Support (TinyMCE) in admin and Front-end for plugins that use wp_editor()
* Option to upload existing Media Library videos to Vimeo in WordPress with one click
* Option to search your Vimeo account for existing videos when embedding video via Gutenberg and TinyMCE
* Option to show only the videos uploaded by the current user in the library page
* Option to enable/disable single video pages that show the Video
* Option to whitelist domains that are allowed to embed the uploaded videos
* Option to control who can view the uploaded videos through the admin or the front-end separately
* Option to select who can view the video in the upload forms (Gutenberg, Classic editor, Vimeo tab or Media Library push buttons)
* Option to edit view privacy in the Media > Vimeo tab for each video
* Option to enable/disable certain embed methods in the Gutenberg and TinyMCE Vimeo Upload forms
* Option to select default folder for videos uploaded in the admin dashboard
* Option to select default folder for videos uploaded from the front-end forms
* Option to edit folder in Media > Vimeo for each video
* Option to select default embed preset for videos uploaded in the admin dashboard
* Option to select default embed preset for videos uploaded from the front-end forms
* Option to edit embed preset in Media > Vimeo for each video
* Update Vimeo Videos from your WordPress site
* Delete Vimeo Videos from your WordPress site
* Experimental Thumbnails support
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

= Version 1.5.4 =

FREE/PRO:

* Added support for WordPress 5.6
* Added dgv_settings_get filter to allow filtering options
* Added dgv_shortcode_output filter to allow filtering the shortcodes
* Improved compatibility with PHP 8

PRO ONLY:
* Added option to disable View Privacy in Gutenberg upload form
* Added option to disable View Privacy in TinyMCE upload form
* Added option to disable View Privacy in other media upload forms
* Improved TinyMCE sweetalert compatibility with Tutor LMS

= Version 1.5.3 =

PRO ONLY:

* Fixed fatal error triggered in the Settings page if the user is not logged in to Vimeo API.

= Version 1.5.2 =

PRO ONLY:

* Fixed incorrect quota calculaton causing the front-end upload to fail
* Fixed Date formatting in the Settings API screen
* Fixed front-end upload validation allowing to continue if failed

= Version 1.5.0 =

FREE/PRO:

* Added problem detection mechanism in the Settings API box. If your connection is missing scopes or is using "Unauthorized" access token warning will appear and instructions how to fix it.
* Added various improvements to the codebase
* Fixed different warnings in the Gutenberg block
* Fixed bug in the Gutenberg block that broke the selection of the current block video after upload
* Fixed bug in the Video edit screen not showing the loader animation
* Improved style of the Video edit page

PRO ONLY:

* Added Embed Preset options in Settings. It's now possible to select default embed preset for both admin/front-end uploads
* Added Embed Preset options in the Video edit screen. It's now possible to change the embed preset of Video
* Added Folders options in Settings. It's now possible to select default folder for both admin/front-end uploads
* Added Folders options in the Video edit screen. It's now possible to change the folder of the Video
* Added view privacy option in Video upload/edit screens
* Added view privacy option in Gutenberg block upload form
* Added view privacy option in TinyMCE upload form
* Added view privacy option to the Media Library local video upload modal
* Added proper warning for the front-end upload in case that the Vimeo connection is missing scopes or using "Unauthorized" access token
* Fixed bug that prevented creating local video entries after upload in the Media Library local video upload modal
* Fixed bug in the Gutenberg block that broke the creation of local entries after upload

= Version 1.4.1 =
* Added dgv_gravityforms_after_vimeo_upload_tasks_dispatched action that is triggered after vimeo upload background tasks are dispatched.

= Version 1.4.0 =
* Refactored the settings backend to improve performance during read/write operations
* Added dgv_uploaded_videos_query_args filter to allow devs to filter the Local Video Library dropdown list in upload modals.
* Improved code quality
* Improved the user dropdown in the Library Screen, added lazy loading.
* Improved translations
* Added experimential thumbnails support for the admin list table screen (PRO)
* Added option to show only the videos uploaded by the current user in the Local Library dropdown in upload forms (PRO)
* Added option to enable/disable video insert methods in Gutenberg and TinyMCE upload forms (PRO)
* Added TinyMCE upload form compatibility for the front-end TinyMCE editors utilizing wp_editor() (eg. found in BuddyPress, LearnDash, etc) (PRO)
* Added dgv_enable_tinymce_upload_plugin filter to allow devs to enable or disable the TinyMCE plugin completely (PRO)
* Deprecate wvv_get_whitelisted_domains(),wvv_get_default_admin_view_privacy(),wvv_get_default_frontend_view_privacy() in favor of the new WP_DGV_Settings_Helper class. (PRO)
* Improved video deletion process (PRO)

= Version 1.3.0 =
* Fix fatal error in the table screen
* Improved Gutenberg Upload block, added error reporting
* Improved Admin Upload form, added error reporting
* Improved front-end upload (PRO)
* Added option to make videos post type publically accessible (PRO)

= Version 1.2.1 =
* Improved front-end upload. Merge tags are now supported: {field:id}, {field:id:title}, {field:id:description} (PRO)
* Imrpoved documentation
* Plugin is now translatable

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