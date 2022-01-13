// Copyright Darko Gjorgjijoski <info@codeverve.com>
// 2020. All Rights Reserved.
// This file is licensed under the GPLv2 License.
// License text available at https://opensource.org/licenses/gpl-2.0.php

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

        if(!jQuery.fn.select2) {
            console.log('Video Uploads for Vimeo: Select2 library is not initialized.');
            return false;
        }

        var translated = {
            errorLoading: function () {
                return DGV.phrases.select2.errorLoading;
            },
            inputTooLong: function (args) {
                var overChars = args.input.length - args.maximum;
                var message = DGV.phrases.select2.inputTooShort;
                message = message.replace('{number}', overChars);
                if (overChars != 1) {
                    message += 's';
                }
                return message;
            },
            inputTooShort: function (args) {
                var remainingChars = args.minimum - args.input.length;
                var message = DGV.phrases.select2.inputTooShort;
                message = message.replace('{number}', remainingChars);
                return message;
            },
            loadingMore: function () {
                return DGV.phrases.select2.loadingMore;
            },
            maximumSelected: function (args) {
                var message = DGV.phrases.select2.maximumSelected;
                message = message.replace('{number}', args.maximum);
                if (args.maximum != 1) {
                    message += 's';
                }
                return message;
            },
            noResults: function () {
                return DGV.phrases.select2.noResults;
            },
            searching: function () {
                return DGV.phrases.select2.searching;
            },
            removeAllItems: function () {
                return DGV.phrases.select2.removeAllItems;
            },
            removeItem: function () {
                return DGV.phrases.select2.removeItem;
            },
            search: function () {
                return DGV.phrases.select2.search;
            }
        }

        var params = {
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                type: 'POST',
                headers: {'Accept': 'application/json'},
                data: function (params) {
                    return {
                        s: params.term,
                    };
                },
                processResults: function (response) {
                    var options = [];
                    if (response.success) {
                        for (var i in response.data) {
                            var id = response.data[i].id;
                            var name = response.data[i].name;
                            options.push({id: id, text: name});
                        }
                    }
                    return {results: options};
                },
                cache: true
            },
            language: translated,
            minimumInputLength: 2,
            width: '100%'
        };

        $.extend(params, opts);
        $(this).select2(params);
        return $(this);
    }


    // Initialize
    var url = DGV.ajax_url + '?action=dgv_user_search&_wpnonce='+ DGV.nonce;
    $(document).find('.dgv-select2').each(function () {
        console.log('initializing select2');
        var params = {};
        var placehodler = $(this).data('placeholder');
        if(placehodler) {
            params.placeholder = placehodler;
        }
        $(this).ajaxSelect(url, params);
    });
    $(document).on('change', '.dgv-select2', function(){
        var value = $(this).val();
        if(value) {
            $('.dgv-clear-selection').show();
        } else {
            $('.dgv-clear-selection').hide();
        }
    });
    $(document).on('click', '.dgv-clear-selection', function(e){
        e.preventDefault();
        var target = $(this).data('target');
        $(target).each(function(e){
            $(this).val(null).trigger('change');
        })
    })

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
        var privacy = DGV.default_privacy;

        var errorHandler = function ($eself, error) {
            var type = 'error';
            var $_notice = $eself.find('.wvv-notice-wrapper');
            if ($_notice.length > 0) {
                $_notice.remove();
            }
            var message = '';
            try {
                var errorObject = JSON.parse(error);
                if(errorObject.hasOwnProperty('invalid_parameters')) {
                    for(var i in errorObject.invalid_parameters) {
                        var msg = errorObject.invalid_parameters[i].error + ' ' + errorObject.invalid_parameters[i].developer_message;
                        message += '<li>'+msg+'</li>';
                    }
                }
                message = '<p style="margin-bottom: 0;font-weight: bold;">'+DGV.correct_errors+':</p>' + '<ul style="list-style: circle;padding-left: 20px;">'+message+'</ul>';
            } catch (e) {
                message = error;
            }

            $eself.prepend(notice(message, type));
            $eself.find('.dgv-loader').css({'display': 'none'});
            $eself.find('button[type=submit]').prop('disabled', false);
        };

        var updateProgressBar = function($pbar, value) {
            if($pbar.is(':hidden')) {
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
                setTimeout(function(){
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
        var data = $self.serialize();
        $.ajax({
            url: DGV.ajax_url + '?action=dgv_handle_settings&_wpnonce=' + DGV.nonce,
            type: 'POST',
            data: data,
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
            }
        });
        return false;
    });
})(jQuery);


// Fix problems
(function($){
    $(document).on('click', '.wvv-problem-fix-trigger', function(e){
        e.preventDefault();
        var $wrap = $(this).closest('.wvv-problem-wrapper');
        var $fixWrap = $wrap.find('.wvv-problem--fix')
        var text = $fixWrap.text();
        swal.fire({
            showCloseButton: true,
            showCancelButton: false,
            showConfirmButton: false,
            html: '<div class="wvv-problem-solution">\n' +
                '\t<h2>'+DGV.problem_solution+'</h2>\n' +
                '\t<p>'+text+'</p>\n' +
                '</div>',
        });
    });
})(jQuery);
