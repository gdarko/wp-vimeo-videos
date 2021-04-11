// Copyright Darko Gjorgjijoski <info@codeverve.com>
// 2020. All Rights Reserved.
// This file is licensed under the GPLv2 License.
// License text available at https://opensource.org/licenses/gpl-2.0.php

(function ($) {

    /**
     * Create shortcode
     * @param uri
     * @returns {string}
     */
    var create_shortcode = function (uri) {
        return '[dgv_vimeo_video id="' + uri + '"]';
    };

    /**
     * Insert in the current editor
     * @param uri
     */
    var insert_in_editor = function (uri) {
        if (!window.hasOwnProperty('currentEditor')) {
            return;
        }
        window.currentEditor.insertContent(create_shortcode(uri));
    }

    tinymce.PluginManager.add('dgv_vimeo_button', function (editor, url) {
        var tmpEditor = editor;
        var params = {
            text: DGV_MCE_Config.phrases.tmce_title,
            classes: 'ed_button button button-small',
            tooltip: DGV_MCE_Config.phrases.tmce_tooltip,
            onclick: function () {
                window.currentEditor = tmpEditor;
                var uploadModal = new WPVimeoVideos.UploaderModal('tinymce');
                uploadModal.open();
            }
        }
        if(DGV_MCE_Config.icon) {
            params.image = DGV_MCE_Config.icon_url;
        }
        editor.addButton('dgv_vimeo_button', params);
    });

    $(window).on('wpdgv.events.insert', function (e, data) {
        if (data.context === 'tinymce') {
            insert_in_editor(data.uri);
        }
    })
    $(window).on('wpdgv.events.upload', function (e, data) {
        if (data.context === 'tinymce') {
            insert_in_editor(data.uri);
        }
    })

})(jQuery);