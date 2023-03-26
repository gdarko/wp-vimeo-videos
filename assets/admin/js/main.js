// Copyright Darko Gjorgjijoski <info@codeverve.com>
// 2020. All Rights Reserved.
// This file is licensed under the GPLv2 License.
// License text available at https://opensource.org/licenses/gpl-2.0.php

window.DGV = window.hasOwnProperty('DGV') ? window.DGV : {};

window.DGV.Loader = '<div class="sweet_loader"><svg viewBox="0 0 140 140" width="140" height="140"><g class="outline"><path d="m 70 28 a 1 1 0 0 0 0 84 a 1 1 0 0 0 0 -84" stroke="rgba(0,0,0,0.1)" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"></path></g><g class="circle"><path d="m 70 28 a 1 1 0 0 0 0 84 a 1 1 0 0 0 0 -84" stroke="#71BBFF" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-dashoffset="200" stroke-dasharray="300"></path></g></svg></div>';


var notice = function (message, type) {
    return '<div class="notice notice-' + type + ' is-dismissible dgv-clear-padding"><p>' + message + '</p></div>\n';
};

(function ($) {
    /**
     * Ajax select plugin
     * @param url
     * @param opts
     * @returns {*|jQuery|HTMLElement}
     */
    $.fn.ajaxSelect = function (url, opts) {

        if (!jQuery.fn.select2) {
            console.log('Video Uploads for Vimeo: Select2 library is not initialized.');
            return false;
        }

        var translated = {
            errorLoading: function () {
                return DGVAdmin.phrases.select2.errorLoading;
            },
            inputTooLong: function (args) {
                var overChars = args.input.length - args.maximum;
                var message = DGVAdmin.phrases.select2.inputTooShort;
                message = message.replace('{number}', overChars);
                if (overChars != 1) {
                    message += 's';
                }
                return message;
            },
            inputTooShort: function (args) {
                var remainingChars = args.minimum - args.input.length;
                var message = DGVAdmin.phrases.select2.inputTooShort;
                message = message.replace('{number}', remainingChars);
                return message;
            },
            loadingMore: function () {
                return DGVAdmin.phrases.select2.loadingMore;
            },
            maximumSelected: function (args) {
                var message = DGVAdmin.phrases.select2.maximumSelected;
                message = message.replace('{number}', args.maximum);
                if (args.maximum != 1) {
                    message += 's';
                }
                return message;
            },
            noResults: function () {
                return DGVAdmin.phrases.select2.noResults;
            },
            searching: function () {
                return DGVAdmin.phrases.select2.searching;
            },
            removeAllItems: function () {
                return DGVAdmin.phrases.select2.removeAllItems;
            },
            removeItem: function () {
                return DGVAdmin.phrases.select2.removeItem;
            },
            search: function () {
                return DGVAdmin.phrases.select2.search;
            }
        }

        var args = {
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                type: 'POST',
                headers: {'Accept': 'application/json'},
                data: function (params) {
                    return {
                        search_str: params.term,
                        page_number: params.page || 1
                    };
                },
                processResults: function (response) {
                    var options = [];
                    if (response.success) {
                        if (response.data.hasOwnProperty('results')) {
                            return response.data;
                        } else {
                            for (var i in response.data) {
                                var id = response.data[i].id;
                                var name = response.data[i].name;
                                options.push({id: id, text: name});
                            }
                        }

                    }
                    return {results: options};
                },
                cache: true
            },
            language: translated,
            minimumInputLength: 2,
            width: '100%',
            allowClear: true,
        };

        $.extend(args, opts);
        $(this).select2(args);
        return $(this);
    }


    // Initialize
    $(document).find('.dgv-select2').each(function () {
        var params = {};
        var placehodler = $(this).data('placeholder');
        var action = $(this).data('action');
        var url = DGV.ajax_url + '?action=' + action + '&_wpnonce=' + DGV.nonce;
        var min_input_len = $(this).data('minInputLength');
        if (placehodler) {
            params.placeholder = placehodler;
        }
        if (min_input_len) {
            params.minimumInputLength = min_input_len;
        }
        $(this).ajaxSelect(url, params);
    });
    $(document).on('change', '.dgv-select2-clearable', function () {
        var value = $(this).val();
        if (value) {
            $('.dgv-clear-selection').show();
        } else {
            $('.dgv-clear-selection').hide();
        }
    });
    $(document).on('click', '.dgv-clear-selection', function (e) {
        e.preventDefault();
        var target = $(this).data('target');
        $(target).each(function (e) {
            $(this).val(null).trigger('change');
        })
    });

    // Intiializ

})(jQuery);

// Handle vimeo upload
(function ($) {

    jQuery('.wvv-video-upload').submit(function (e) {

        var $self = $(this);
        var $loader = $self.find('.dgv-loader');
        var $submit = $self.find('button[type=submit]');
        var $progressBar = $self.find('.dgv-progress-bar');

        var formData = new FormData(this);
        var videoFile = formData.get('vimeo_video');

        if (!WPVimeoVideos.Uploader.validateVideo(videoFile)) {
            swal.fire(DGV.sorry, DGV.upload_invalid_file, 'error');
            return false;
        }

        var title = formData.get('vimeo_title');
        var description = formData.get('vimeo_description');
        var privacy = formData.get('vimeo_view_privacy');
        if (!privacy) {
            privacy = DGV.default_privacy;
        }

        var errorHandler = function ($eself, error) {
            var type = 'error';
            var $_notice = $eself.find('.wvv-notice-wrapper');
            if ($_notice.length > 0) {
                $_notice.remove();
            }
            var message = '';
            try {
                var errorObject = JSON.parse(error);
                if (errorObject.hasOwnProperty('invalid_parameters')) {
                    for (var i in errorObject.invalid_parameters) {
                        var msg = errorObject.invalid_parameters[i].error + ' ' + errorObject.invalid_parameters[i].developer_message;
                        message += '<li>' + msg + '</li>';
                    }
                }
                message = '<p style="margin-bottom: 0;font-weight: bold;">' + DGV.correct_errors + ':</p>' + '<ul style="list-style: circle;padding-left: 20px;">' + message + '</ul>';
            } catch (e) {
                message = error;
            }

            $eself.prepend(notice(message, type));
            $eself.find('.dgv-loader').css({'display': 'none'});
            $eself.find('button[type=submit]').prop('disabled', false);
        };

        var updateProgressBar = function ($pbar, value) {
            if ($pbar.is(':hidden')) {
                $pbar.show();
            }
            $pbar.find('.dgv-progress-bar-inner').css({width: value + '%'})
            $pbar.find('.dgv-progress-bar-value').text(value + '%');
        };

        var uploader = new WPVimeoVideos.Uploader(DGV.access_token, videoFile, {
            'title': title,
            'description': description,
            'privacy': privacy,
            'wp': {
                'notify_endpoint': DGV.ajax_url + '?action=dgv_store_upload&_wpnonce=' + DGV.nonce,
            },
            'beforeStart': function () {
                $loader.css({'display': 'inline-block'});
                $submit.prop('disabled', true);
            },
            'onProgress': function (bytesUploaded, bytesTotal) {
                var percentage = (bytesUploaded / bytesTotal * 100).toFixed(2);
                updateProgressBar($progressBar, percentage);
            },
            'onSuccess': function (response, currentUpload) {
                var type = response.success ? 'success' : 'error';
                var message = response.data.message;
                var $_notice = $self.find('.wvv-notice-wrapper');
                if ($_notice.length > 0) {
                    $_notice.remove();
                }
                $self.prepend(notice(message, type));
                setTimeout(function () {
                    $self.get(0).reset();
                    $loader.css({'display': 'none'});
                    $submit.prop('disabled', false);
                    updateProgressBar($progressBar, 0);
                    $progressBar.hide();
                }, 1000);
            },
            'onError': function (error) {
                errorHandler($self, error);
            },
            'onVideoCreateError': function (error) {
                errorHandler($self, error);
            },
            'onWPNotifyError': function (error) {
                errorHandler($self, error);
            }
        });
        uploader.start();
        return false;
    });

})(jQuery);

// Handle vimeo settings
(function ($) {
    $('#dg-vimeo-settings').submit(function (e) {
        var $self = $(this);
        var $btn = $self.find('button[type=submit]');
        var data = $self.serialize();
        $.ajax({
            url: DGV.ajax_url + '?action=dgv_handle_settings&_wpnonce=' + DGV.nonce,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $btn.prepend('<span class="dashicons dashicons-update dgv-dashicon dgv-spin"></span>');
            },
            success: function (response) {
                var message;
                var type;
                if (response.success) {
                    message = response.data.message;
                    type = 'success';
                    if (response.data.hasOwnProperty('api_info')) {
                        $self.find('.vimeo-info-wrapper').html(response.data.api_info);
                    }
                } else {
                    message = response.data.message;
                    type = 'error';
                }
                var $_nwrapper = $self.closest('.wrap').find('.wvv-notice-wrapper');
                if ($_nwrapper.length > 0) {
                    $_nwrapper.html('');
                }
                $_nwrapper.prepend(notice(message, type));
            },
            complete: function () {
                var $icon = $btn.find('.dgv-dashicon');
                $icon.removeClass('dashicons-update dgv-spin').addClass('dashicons-yes')
                setTimeout(function () {
                    $icon.detach().remove();
                }, 1000);
            }
        });
        return false;
    });
})(jQuery);

// Delete Videos
(function ($) {

    function deleteVideo(vimeo_uri, post_id, context, $source) {
        swal.fire({
            title: DGV.delete_confirm_title,
            text: DGV.delete_confirm_desc,
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: DGV.confirm,
            cancelButtonText: DGV.cancel,
            showLoaderOnConfirm: true,
            preConfirm: function () {
                return new Promise(function (resolve, reject) {
                    $.ajax({
                        type: "POST",
                        url: DGV.ajax_url + '?action=dgv_handle_delete&_wpnonce=' + DGV.nonce,
                        data: {vimeo_uri: vimeo_uri, post_id: post_id},
                        success: function (response) {
                            resolve(response);
                        },
                        error: function () {
                            reject("HTTP Error");
                        }
                    });
                })
            },
            allowOutsideClick: false
        }).then(function (response) {

            if (response.value.data.hasOwnProperty('local_delete') && response.value.data.local_delete) {
                if (context === 'list') {
                    if ($source) {
                        $source.closest('tr').detach().remove();
                    }
                }
            }

            if (response.value.success) {
                swal.fire({
                    title: response.value.data.title,
                    type: 'success',
                    text: response.value.data.message,
                    showCancelButton: false,
                    confirmButtonText: DGV.close,
                    allowOutsideClick: false
                })
            } else {
                swal.fire({
                    title: response.value.data.title,
                    type: 'error',
                    text: response.value.data.message,
                    showCancelButton: false,
                    confirmButtonText: DGV.close,
                    allowOutsideClick: false
                })
            }
        });
    }

    $(document).on('click', '.dg-vimeo-delete', function (e) {
        var can_delete = $(this).data('can-delete');
        var uri = $(this).data('vimeo-uri');
        var id = $(this).data('id');
        if (!can_delete) {
            swal.fire(DGV.sorry, DGV.delete_not_allowed, 'error');
        } else {
            deleteVideo(uri, id, 'list', $(this));
        }
    })
})(jQuery);

// Conditional Love
(function ($) {
    $(document).on('change', '.dgv-conditional-field', function (e) {
        var targetClass = $(this).data('target');
        var currentValue = $(this).val();
        var targetConditionValue = $(this).data('show-target-if-value');
        if (currentValue === targetConditionValue) {
            $(targetClass).show();
        } else {
            $(targetClass).hide();
        }
    });
})(jQuery);


// Edit Video
(function ($) {

    // Save :: Basic Information
    $(document).on('submit', '#dgv-video-save-basic', function (e) {
        var data = $(this).serialize();
        var $btn = $(this).find('button[type=submit]');
        $.ajax({
            url: DGV.ajax_url + '?action=dgv_handle_basic_edit&_wpnonce=' + DGV.nonce,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $btn.prepend('<span class="dashicons dashicons-update dgv-dashicon dgv-spin"></span>');
            },
            success: function (response) {
                var title = response.success ? DGV.success : DGV.sorry;
                var type = response.success ? 'success' : 'error';
                swal.fire(title, response.data.message, type);
            },
            error: function () {
                swal.fire(DGV.sorry, DGV.http_error, 'error');
            },
            complete: function () {
                var $icon = $btn.find('.dgv-dashicon');
                $icon.removeClass('dashicons-update dgv-spin').addClass('dashicons-yes')
                setTimeout(function () {
                    $icon.detach().remove();
                }, 1000);
            }
        });
        return false
    });

    // Input :: Embed Privacy
    $(document).on('input', '#privacy_embed_domain', function (e) {
        var $form = $(this).closest('form');
        var $add = $form.find('button[name=admin_action][value=add_domain]');
        var value = $(this).val();
        if ($add.length) {
            var is_disabled = value === '';
            $add.prop('disabled', is_disabled);
        }
    });

    // Save :: Embed Privacy
    $(document).on('submit', '#dgv-video-save-embed-privacy', function (e) {
        var data = $(this).serialize();
        var $btn = $(this).find('button[type=submit].button-primary');
        var $domain_list = $(this).find('.privacy-embed-whitelisted-domains');
        $.ajax({
            url: DGV.ajax_url + '?action=dgv_handle_embed_privacy&_wpnonce=' + DGV.nonce,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $btn.prepend('<span class="dashicons dashicons-update dgv-dashicon dgv-spin"></span>');
            },
            success: function (response) {
                var type = response.success ? 'success' : 'error';
                var domain = response.data.domain_added;
                if (response.success) {
                    if (response.data.hasOwnProperty('domain_added') && $domain_list.html().indexOf(domain) < 0) {
                        $domain_list.append('<li>' + domain + ' <a href="#" class="submitdelete dgv-delete-domain" data-domain="' + domain + '" data-uri="' + response.data.uri + '">(' + DGV.remove_lower + ')</a></li>')
                    }
                }
                if (response.data.hasOwnProperty('message')) {
                    var title = response.success ? DGV.success : DGV.sorry;
                    swal.fire(title, response.data.message, type);
                }
            },
            error: function () {
                swal.fire(DGV.sorry, DGV.http_error, 'error');
            },
            complete: function () {
                var $icon = $btn.find('.dgv-dashicon');
                $icon.removeClass('dashicons-update dgv-spin').addClass('dashicons-yes')
                setTimeout(function () {
                    $icon.detach().remove();
                }, 1000);
            }
        });

        return false;
    });

    // Save :: Delete domain
    $(document).on('click', '.dgv-delete-domain', function (e) {
        e.preventDefault();

        var $item = $(this).closest('li');

        var domain = $(this).data('domain');
        var uri = $(this).data('uri');

        var data = {'domain': domain, 'uri': uri};

        $.ajax({
            url: DGV.ajax_url + '?action=dgv_delete_embed_privacy_domain&_wpnonce=' + DGV.nonce,
            type: 'POST',
            data: data,
            success: function (response) {

                if (response.success) {
                    $item.detach().remove();
                } else {
                    swal.fire(DGV.sorry, DGV.delete_whitelist_domain_error, 'error');
                }

            },
            error: function () {
                swal.fire(DGV.sorry, DGV.http_error, 'error');
            }
        });
    });

    // Remove
    setTimeout(function () {
        $(document).find('.dgv-embed-container .fluid-width-video-wrapper').removeClass('fluid-width-video-wrapper');
    }, 100);

    // Save Folders (Since 1.5.0)
    $(document).on('submit', '#dgv-video-save-folders', function (e) {
        var data = $(this).serialize();
        var $btn = $(this).find('button[type=submit]');
        $.ajax({
            url: DGV.ajax_url + '?action=dgv_handle_video_folder_set&_wpnonce=' + DGV.nonce,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $btn.prepend('<span class="dashicons dashicons-update dgv-dashicon dgv-spin"></span>');
            },
            success: function (response) {
                var title = response.success ? DGV.success : DGV.sorry;
                var type = response.success ? 'success' : 'error';
                swal.fire(title, response.data.message, type);
            },
            complete: function () {
                var $icon = $btn.find('.dgv-dashicon');
                $icon.removeClass('dashicons-update dgv-spin').addClass('dashicons-yes')
                setTimeout(function () {
                    $icon.detach().remove();
                }, 1000);
            }
        });
        return false;
    });

    // Save embed preset (Since 1.5.0)
    $(document).on('submit', '#dgv-video-save-embed-preset', function (e) {
        var data = $(this).serialize();
        var $btn = $(this).find('button[type=submit]');
        $.ajax({
            url: DGV.ajax_url + '?action=dgv_handle_video_embed_preset_set&_wpnonce=' + DGV.nonce,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $btn.prepend('<span class="dashicons dashicons-update dgv-dashicon dgv-spin"></span>');
            },
            success: function (response) {
                var title = response.success ? DGV.success : DGV.sorry;
                var type = response.success ? 'success' : 'error';
                swal.fire(title, response.data.message, type);
            },
            complete: function () {
                var $icon = $btn.find('.dgv-dashicon');
                $icon.removeClass('dashicons-update dgv-spin').addClass('dashicons-yes')
                setTimeout(function () {
                    $icon.detach().remove();
                }, 1000);
            }
        });
        return false;
    });


})(jQuery);


// UPLOAD Attachment: Button Popup
(function ($) {

    $(document).on('click', '.dgv-upload-attachment', function (e) {
        var id = $(this).data('id');

        var privacy_option = '';

        if (DGV.upload_form_options.enable_privacy_option) {
            var view_privacy_opts = '';
            for (var key in DGV.upload_form_options.privacy_view) {
                var name = DGV.upload_form_options.privacy_view[key].name;
                var is_available = DGV.upload_form_options.privacy_view[key].available;
                var is_default = DGV.upload_form_options.privacy_view[key].default;
                var disabled = is_available ? '' : 'disabled';
                var selected = is_default ? 'selected' : '';
                view_privacy_opts += '<option ' + disabled + ' ' + selected + ' value="' + key + '">' + name + '</option>';
            }
            privacy_option = '    <div class="dgv-vimeo-form-row">\n' +
                '        <label for="vimeo_view_privacy">' + DGV.privacy_view + '</label>\n' +
                '        <select class="form-control wvv-w-100" name="vimeo_view_privacy" id="vimeo_view_privacy">' + view_privacy_opts + '</select>' +
                '    </div>\n'
        }

        swal.fire({
            showCloseButton: true,
            showCancelButton: false,
            showConfirmButton: false,
            allowOutsideClick: false,
            html: '<form class="dgv-media-library-upload dgv-text-left" method="POST">\n' +
                '    <div class="dgv-vimeo-form-row">\n' +
                '        <h4 for="vimeo_title">' + DGV.upload_to_vimeo + '</h4>\n' +
                '    </div>\n' +
                '    <div class="dgv-vimeo-form-row">\n' +
                '        <label for="vimeo_title">' + DGV.title + '</label>\n' +
                '        <input type="text" class="form-control" name="vimeo_title" id="vimeo_title">\n' +
                '    </div>\n' +
                '    <div class="dgv-vimeo-form-row">\n' +
                '        <label for="vimeo_description">' + DGV.description + '</label>\n' +
                '        <textarea class="form-control" rows="5" name="vimeo_description" id="vimeo_description"></textarea>' +
                '    </div>\n' + privacy_option +
                '    <div class="dgv-vimeo-form-row dgv-vimeo-form-row-footer">\n' +
                '        <input type="hidden" name="attachment_id" value="' + id + '">\n' +
                '        <div class="dgv-loader dgv-loader-inline" style="display: none;"></div>' +
                '        <button type="submit" name="vimeo_upload" class="button-primary button-small" value="1">' + DGV.upload + '</button>\n' +
                '    </div>\n' +
                '</form>'
        });
    });
})(jQuery);

// UPLOAD Attachment: Form Handler
(function ($) {
    $(document).on('submit', '.dgv-media-library-upload', function (e) {
        var data = $(this).serialize();
        var $inputID = $(this).find('input[name=attachment_id]');
        var $button = $(this).find('button[type=submit]');
        var ID = $inputID.val();
        var $self = $(this);
        $.ajax({
            url: DGV.ajax_url + '?action=dgv_attachment2vimeo&_wpnonce=' + DGV.nonce,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $self.find('.dgv-loader').show();
                $button.prop('disabled', true);
            },
            success: function (response) {
                if (response.success) {
                    var metabox_html = response.data.info_metabox_html;
                    var $metabox = $('#dgv-mlmb-' + ID);
                    if ($metabox.length > 0) {
                        $metabox.html(metabox_html);
                    }
                    swal.fire(DGV.success, response.data.message, 'success');
                } else {
                    swal.fire(DGV.error, response.data.message, 'error');
                }
                $self.find('.dgv-loader').hide();
            },
            complete: function () {
                $self.find('.dgv-loader').hide();
                $button.prop('disabled', false);
            },
            error: function () {
                $self.find('.dgv-loader').hide();
                $button.prop('disabled', false);
                swal.fire(DGV.error, DGV.http_error, 'error');
            }
        });

        return false;
    });
})(jQuery);


// DELETE Vimeo Attachment: Button
(function ($) {

    $(document).on('click', '.dgv-delete-attachment', function (e) {
        var id = $(this).data('id');

        swal.fire({
            showCloseButton: true,
            showCancelButton: false,
            showConfirmButton: false,
            allowOutsideClick: false,
            html: '<form class="dgv-media-library-delete dgv-text-left" method="POST">\n' +
                '\t<div class="dgv-vimeo-form-row">\n' +
                '\t\t<label for="vimeo_title">' + DGV.delete_confirmation + '</label>\n' +
                '\t</div>\n' +
                '\t<div class="dgv-vimeo-form-row">\n' +
                '\t<input type="hidden" name="attachment_id" value="' + id + '">\n' +
                '\t<div class="dgv-loader dgv-loader-inline" style="display: none;"></div>' +
                '\t<button type="submit" name="delete" class="button-primary" value="1">' + DGV.delete_confirmation_yes + '</button>\n' +
                '\t</div>\n' +
                '</form>'
        });
    });

})(jQuery);

// UPLOAD Attachment: Form Handler
(function ($) {
    $(document).on('submit', '.dgv-media-library-delete', function (e) {
        var $inputID = $(this).find('input[name=attachment_id]');
        var $button = $(this).find('button[type=submit]');
        var ID = $inputID.val();
        var $self = $(this);
        var data = $self.serialize();
        $.ajax({
            url: DGV.ajax_url + '?action=dgv_attachment2vimeo_delete&_wpnonce=' + DGV.nonce,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $self.find('.dgv-loader').show();
                $button.prop('disabled', true);
            },
            success: function (response) {
                if (response.success) {
                    var metabox_html = response.data.info_metabox_html;
                    var $metabox = $('#dgv-mlmb-' + ID);
                    if ($metabox.length > 0) {
                        $metabox.html(metabox_html);
                    }
                    swal.fire(DGV.success, response.data.message, 'success');
                } else {
                    swal.fire(DGV.error, response.data.message, 'error');
                }
                $self.find('.dgv-loader').hide();
            },
            complete: function () {
                $self.find('.dgv-loader').hide();
                $button.prop('disabled', false);
            },
            error: function () {
                $self.find('.dgv-loader').hide();
                $button.prop('disabled', false);

                swal.fire(DGV.error, DGV.http_error, 'error');
            }
        });

        return false;
    });
})(jQuery);

// Fix problems
(function ($) {
    $(document).on('click', '.wvv-problem-fix-trigger', function (e) {
        e.preventDefault();
        var $wrap = $(this).closest('.wvv-problem-wrapper');
        var $fixWrap = $wrap.find('.wvv-problem--fix')
        var text = $fixWrap.text();
        swal.fire({
            showCloseButton: true,
            showCancelButton: false,
            showConfirmButton: false,
            html: '<div class="wvv-problem-solution">\n' +
                '\t<h2>' + DGV.problem_solution + '</h2>\n' +
                '\t<p>' + text + '</p>\n' +
                '</div>',
        });
    });
})(jQuery);


(function ($) {

    function fallbackCopyTextToClipboard(text) {
        var textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            var successful = document.execCommand('copy');
            var msg = successful ? 'successful' : 'unsuccessful';
        } catch (err) {
            console.error('Fallback: Oops, unable to copy', err);
        }
        document.body.removeChild(textArea);
    }

    function copyTextToClipboard(text) {
        if (!navigator.clipboard) {
            fallbackCopyTextToClipboard(text);
            return;
        }
        navigator.clipboard.writeText(text).then(function () {
            console.log('Async: Copying to clipboard was successful!');
        }, function (err) {
            console.error('Async: Could not copy text: ', err);
        });
    }

    $(document).on('click', '.dgv-copy-embed-code', function (e) {
        var $self = $(this);
        var text = $self.closest('.column-embed').find('.embed-code').text().trim();
        copyTextToClipboard(text);
        $self.removeClass('dashicons-admin-links');
        $self.addClass('dashicons-yes')
        setTimeout(function () {
            $self.addClass('dashicons-admin-links');
            $self.removeClass('dashicons-yes')
        }, 2000);
    });

})(jQuery);


window.addEventListener('DOMContentLoaded', (event) => {
    window.DGV.AdminStats = function () {

        this.init = function() {
            // Trigger Action
            var stats_action = document.getElementById('dgv-vimeo-stats');
            if (stats_action) {
                stats_action.addEventListener('click', function (e) {
                    e.preventDefault();
                    var http = new window.DGV.Http()
                    http.get(DGV.ajax_url, {
                        data: {
                            action: 'dgv_generate_stats',
                            _wpnonce: DGV.nonce
                        },
                        beforeStart: function() {
                            swal.fire({
                                html: '<h4>'+DGV.loading+'</h4>',
                                onRender: function() {
                                    document.querySelector('.swal2-content').prepend(window.DGV.Loader);
                                }
                            })
                        },
                        success: function(response) {
                            swal.fire({
                                html: response.data.html,
                                showCloseButton: true,
                                showCancelButton: false,
                                showConfirmButton: false
                            })
                        },
                        complete: function() {

                        }
                    })
                });
            }
        }

        this.init();

    };

    window.DGV.AdminSettings = function() {
        this.init = function() {
            var infoFields = document.querySelectorAll('.dgv-settings-info');
            console.log(infoFields);
            for(var i = 0; i < infoFields.length; i++) {
                infoFields[i].addEventListener('click', function(e){
                    swal.fire({
                        showCloseButton: true,
                        showCancelButton: false,
                        showConfirmButton: false,
                        html: '<div class="wvv-problem-solution">\n' +
                            '\t<h2>' + DGV.explanation + '</h2>\n' +
                            '\t<p>' + this.dataset.info + '</p>\n' +
                            '</div>',
                    });
                }.bind(infoFields[i]));
            }
        }
        this.init();
    }

    // Initialization

    new window.DGV.AdminStats();
    new window.DGV.AdminSettings();

});
















