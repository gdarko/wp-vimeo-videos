=== Video Uploads for Vimeo ===
Contributors: DarkoG
Tags: vimeo, video, upload vimeo, embed video, upload
Requires at least: 4.2
Stable Tag: 1.7.6
Requires PHP: 5.5.0
Tested up to: 5.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Embed and upload videos to Vimeo directly from your WordPress site

== Description ==

**Integrates your WordPress site with Vimeo using the Vimeo API and allows the user to upload videos directly from WordPress. 8-)**

> **Disclaimer:** I don't work for Vimeo and the plugin is not official Vimeo software. It just uses Vimeo Developer API to provide interface for uploading videos directly from you WordPress site.

Especially useful in the following cases:

* If you want to speed up the entire process. No need to login to Vimeo, you can now upload videos to Vimeo directly from WordPress.
* If you don't want to share your Vimeo login credentials with other people especially when running multi-author blog.
* If you want to accept videos in the front-end forms (WPForms or GravityForms) uploaded directly to your Vimeo account (feature available in PRO version)

=== How it works ===

In order to be able to connect to Vimeo you will need to sign up on <a target="_blank" href="https://developer.vimeo.com/">Vimeo Developer Portal</a> and request access to the Vimeo API. Please check the Installation tab and also the **Screenshot #5**.

<a href="https://docs.codeverve.com/video-uploads-for-vimeo" target="_blank">Detailed Guide</a>

=== Features  ===

* Upload videos from the Media screen
* Upload videos from the Gutenberg editor
* Upload videos from the Classic/TinyMCE editor (**NEW!**)
* Responsive embeds from the Gutenberg editor
* Responsive embeds from the Classic editor
* **"Media > Vimeo"** page is accessible by the users that have the capability upload_files (Author, Editor, Administrators by default)
* **"Settings > Vimeo"** page is accessible by the users that have the capability manage_options (Administrators by default)
* Shortcode available [dgv_vimeo_video id="the_vimeo_id"]
* Useful API information and tips in the "Settings > Vimeo"
* Potentional problem detection tool in "Settings > Vimeo" page

=== Premium Version ===

Core premium features are: **Front-end upload via GravityForms and WPForms**, **Embed Privacy**, **View Privacy**, **Folders** and **Embed Presets management** and a lot more.

The following is full list of additional features:

* Front-end upload via **GravtiyForms** (**NEW!** - now supports chunked uploads with progress bar via the "Modern" field)
* Front-end upload via **WPForms Lite/Premium** (**NEW!** - now supports chunked uploads with progress bar when Theme is set to "Modern")
* Enhanced Gutenberg Support with additional options (eg. account video search)
* Enhanced TinyMCE / Classic ("Vimeo" button is available everywhere - not only in admin, supports account video search)
* TutorLMS / TutorLMS PRO integration with course builder in frontend and backend
* Embed Privacy - Easily configure whitelisted domains that are allowed to embed the uploaded videos
* Embed Privacy - Easily set/modify the embed privacy in the "Media > Vimeo" tab for each video
* Veiw Privacy - Easily configure who can view the uploaded videos (separate options for videos uploaded via frontend, admin side or push buttons)
* View Privacy - Easily set/modify the view privacy in the "Media > Vimeo" tab for each video
* Folders - Option to select default folder for videos uploaded in the admin dashboard
* Folders - Option to select default folder for videos uploaded from the front-end forms
* Folders - Option to modify the folder of uploaded videos through the video "Media > Vimeo" edit page
* Embed Presets - Option to select default embed preset for videos uploaded in the admin dashboard (Applies to Vimeo.com PRO or higher plan accounts)
* Embed Presets - Option to select default embed preset for videos uploaded from the front-end forms (Applies to Vimeo.com PRO or higher plan accounts)
* Embed Presets Option to modify the embed preset of uploaded videos through the video "Edit" page (Applies to Vimeo.com PRO or higher plan accounts)
* Option to upload existing Media Library videos to Vimeo in WordPress with one click from Media Library list table view
* Option to search your Vimeo account for existing videos when inserting video via Gutenberg and TinyMCE blocks
* Option to enable/disable certain embed methods in the Gutenberg and TinyMCE Vimeo Upload forms
* Option to enable/disable creation of single Video pages that show the uploaded video (separate options for videos uploaded via frontend and admin side)
* Option to show only the videos uploaded by the current user in the Media Library page
* Update Vimeo videos directly from your WordPress
* Delete Vimeo videos from your WordPress site
* Experimental Thumbnails support
* Automatic PRO version updates if using valid key from our central repository
* Fast Dedicated Support for premium customers

 <a href="https://codeverve.com/video-uploads-for-vimeo" target="_blank">Get Premium Version</a>

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

No, you must have Client ID, Client Secret and Access Token with the required scopes/permissions. Please check **Screenshot #5** for more details about the setup.

= Which API scopes are required =

Most of them. Especially if you are using the premium version. So it's best to select the following at least: public, private, create, edit, delete, upload, video_files, interact

= Do i need to do any tweaks to the hosting configuration for bigger files ? =

The files are streamed directly from your browser to Vimeo using the TUS protocol, so the upload process doesn't rely on the server where your site is hosted anymore. Therefore no need to adjust any settings.

== Screenshots ==

1. Main upload form
2. Gutenberg upload block
3. Classic/TinyMCE editor support
4. Settings screen with useful API information
5. Example Vimeo Developer APP and how to get the required access keys.
6. Problem detection mechanism
7. Available options (PRO version)
8. Video edit screen (PRO version)
9. WPForms Integration (PRO version)
10. GravityForms Integration (PRO version)

== Changelog ==

= Version 1.7.6 =
- Various Security improvements
- Plugin rename to "Video Uploads for Vimeo"

= Version 1.7.3 =
- Fixed user filter dropdown in Media > Vimeo
- Fixed user admin url when clicking on the author in Media > Vimeo
- Improved the TinyMCE upload modal styling
- Updated sweetalert2 from version 8.17.1 to version 11.1.4 (latest)
- Updated and resynced the .pot i18n template to add the new strings

_PRO ONLY:_
- Added enhanced LeardDash integration. Besides the Gutenberg & Classic editor integrations, it now integrates in "Video Progression" field in Topic/Lesson settings.
- Added merge tags support for the GravityForms integration
- Added support for Live Pro, Live Business, Live Premium, Producer plans
- Fixed Vimeo deletion. It failed to delete remote (vimeo.com) video when user deletes Video form the admin screens
- Fixed fatal error triggered when activating the plugin

= Version 1.7.2 =
* Fixed a lot of problems related to lowercase HTTPv2 headers for users that use HTTPv2
* Improved 'Hide videos uploaded from different authors for non-admin use" option in the Gutenberg and TinyMCE blocks
* Removed xdebug ini_set/get calls in the logger. The directive to overload var_dump was removed in xdebug.
* Updated the underlying PHP libraries to their latest versions

_PRO ONLY:_
* Improved GravityForms front-end processing
* Improved logging in the front-end implementations

= Version 1.7.1 =
* Added additional check if local video is already created, if so, skip creation process in db->create_local_video().
* Added dgv_before_create_api_video_params filter for filtering the API parameters before remote Video is created. <a target="_blank" href="https://github.com/gdarko/wp-vimeo-videos/issues/26#issuecomment-852452619">More info here</a>
* Added dgv_before_create_local_video_params filter for filtering the parameters before local Video is created. <a target="_blank" href="https://github.com/gdarko/wp-vimeo-videos/issues/26#issuecomment-852452619">More info here</a>
* Fixed author filter in tinymce / gutenberg upload modal when show videos of current author option is enabled.
* Fixed a bug when editing a video title, it was not editing the local video title

_PRO ONLY:_

* Fixed GravityForms integration problem caused by third party plugins making the uploads to fail.

= Version 1.7.0 =

* Added size column in Vimeo videos admin table
* Added metadata caching functionality. The plugin now caches the duration, size, dimensions of a video. Useful for future development.
* Added a lot of improvements to the logging feature. Logs can be found in uploads/wp-vimeo-videos/debug.log
* Added dgv_upload_modal_title filter for filtering the Upload modal title
* Added dgv_mce_toolbar_icon_enable filter for enabling or disabling TinyMCE vimeo icon
* Added dgv_mce_toolbar_tooltip filter for filtering the TinyMCE toolbar tooltip
* Added dgv_mce_toolbar_icon_url filter for filtering the default Vimeo logo icon
* Fixed a bug when the view privacy is 'Only those with link'. Video links were pointing to wrong link, instead of private video link
* Renamed dgv_toolbar_title to dgv_mce_toolbar_title. The filter can be used to change the TinyMCE toolbar title (default: Vimeo). Return empty string to remove it
* Refactored post upload hooks
* Link author to its WordPress user profile page in Vimeo videos admin table

_PRO ONLY:_

* Added APIs that can be used to integrate chunked progress bar uploads in custom forms
* Added [dgv_user_uploads user=current allow_delete=1 allow_edit=1] shortcode that lists the current user uploads and allows the logged in user to manage those if the user is owner of the videos
* Added diagnostics and error messages if access token doesn't support Interact scope. Interact is required for the for the folders functionality.
* Switch input field to textarea for description in front-end upload integrations
* Added dgv_frontend_after_upload_gravityforms action triggered after front-end upload using GravityFroms
* Added dgv_frontend_after_upload_wpforms action triggered after front-end upload using WPForms
* Fixed problem with the chunked progress-bar front-end upload in GravityForms.
* Revamped product activation system

= Version 1.6.0 =

* Added TinyMCE / Classic editor support
* General code improvements
* Fixed various PHP warnings
* Improved upload form style
* Improved public video embed (added black color as background)
* Disabled ESC key close action on Classic Editor / TinyMCE upload form modal
* Re-synced pot files

_PRO ONLY:_

* Added WPForms integration
* Added chunked upload support through WPForms (See "Modern" theme in Video Uploads for Vimeo field)
* Added chunked upload support through GravityForms (See "Modern" field)
* Added progress bar support on the chunked uploads via WPForms/GravityForms
* Improved the codebase, rewritten front-end background processing for easier integrations in future
* Fixed various PHP warnings
* Added missing text domain on some strings

= Version 1.5.7 =

_PRO ONLY:_

* Add Vimeo Upload support in TutorLMS lesson builder
* Downgrade Guzzle HTTP client to 6.5.5 to avoid conflicts with other plugins that mostly use version 6.5.5

= Version 1.5.6 =

* Added missing .map file for the tus-js-client library
* Updated the version of the-js-client tus and moved into separate folder
* Added translations/i18n compatibility for the "Filter" dropdown in Vimeo screen
* Added missing textdomain to some of the strings
* Re-synced the .pot file

_PRO ONLY:_

* Added TutorLMS compatibility
* Added missing asterisk for required file field in GravityForms Front-end upload
* Added option to re-use the upload modal used for TinyMCE for integrations with other plugins. Upload modal is now standalone
* Added default settings import on plugin activation
* Fixed a bug when "Embed domains" is empty, it was making the Video embed privacy as "whitelist", but no whitelisted domains were added

= Version 1.5.4 =

* Added support for WordPress 5.6
* Added dgv_settings_get filter to allow filtering options
* Added dgv_shortcode_output filter to allow filtering the shortcodes
* Improved compatibility with PHP 8

_PRO ONLY:_

* Added option to disable View Privacy in Gutenberg upload form
* Added option to disable View Privacy in TinyMCE upload form
* Added option to disable View Privacy in other media upload forms
* Improved TinyMCE sweetalert compatibility with Tutor LMS

= Version 1.5.3 =

* Fixed fatal error triggered in the Settings page if the user is not logged in to Vimeo API.

= Version 1.5.2 =

_PRO ONLY:_

* Fixed incorrect quota calculaton causing the front-end upload to fail
* Fixed Date formatting in the Settings API screen
* Fixed front-end upload validation allowing to continue if failed

= Version 1.5.0 =

* Added problem detection mechanism in the Settings API box. If your connection is missing scopes or is using "Unauthorized" access token warning will appear and instructions how to fix it.
* Added various improvements to the codebase
* Fixed different warnings in the Gutenberg block
* Fixed bug in the Gutenberg block that broke the selection of the current block video after upload
* Fixed bug in the Video edit screen not showing the loader animation
* Improved style of the Video edit page

_PRO ONLY:_

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

_PRO ONLY:_

* Added dgv_gravityforms_after_vimeo_upload_tasks_dispatched action that is triggered after vimeo upload background tasks are dispatched.

= Version 1.4.0 =
* Refactored the settings backend to improve performance during read/write operations
* Added dgv_uploaded_videos_query_args filter to allow devs to filter the Local Video Library dropdown list in upload modals.
* Improved code quality
* Improved the user dropdown in the Library Screen, added lazy loading.
* Improved translations

_PRO ONLY:_

* Added experimential thumbnails support for the admin list table screen
* Added option to show only the videos uploaded by the current user in the Local Library dropdown in upload forms
* Added option to enable/disable video insert methods in Gutenberg and TinyMCE upload forms
* Added TinyMCE upload form compatibility for the front-end TinyMCE editors utilizing wp_editor() (eg. found in BuddyPress, LearnDash, etc)
* Added dgv_enable_tinymce_upload_plugin filter to allow devs to enable or disable the TinyMCE plugin completely
* Deprecate wvv_get_whitelisted_domains(),wvv_get_default_admin_view_privacy(),wvv_get_default_frontend_view_privacy() in favor of the new WP_DGV_Settings_Helper class.
* Improved video deletion process.

= Version 1.3.0 =

* Fix fatal error in the table screen
* Improved Gutenberg Upload block, added error reporting
* Improved Admin Upload form, added error reporting

_PRO ONLY:_

* Improved front-end upload
* Added option to make videos post type publically accessible

= Version 1.2.1 =

* Imrpoved documentation
* Plugin is now translatable

_PRO ONLY:_

* Improved front-end upload. Merge tags are now supported: {field:id}, {field:id:title}, {field:id:description}

= Version 1.2.0 =

* Added direct link in the admin Vimeo list table
* Added instructions/welcome screen
* Added option to enable/disable view access in the list table if the user didn't uploaded the video
* Added author filter on the videos list table
* Added author column on the videos list table
* Added performance tweaks
* Added UI improvements in dashboard screens
* Added useful information in the Settings screen
* Updated examples

_PRO ONLY:_

* Added tinyMCE/Classic Editor support
* Added front-End upload support for GravityForms via field
* Added Vimeo Video Delete Option
* Added Vimeo Video Privacy Management
* Added option to upload existing media library videos to Vimeo
* Added option to search your Vimeo account when embedding video

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
