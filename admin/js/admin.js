var notice = function (message, type) {
    return '<div class="wvv-notice-wrapper form-row"><div class="notice notice-' + type + ' is-dismissible dgv-clear-padding"><p>' + message + '</p></div></div>\n';
};

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
            var message = error;
            var type = 'error';
            var $_notice = $eself.find('.wvv-notice-wrapper');
            if ($_notice.length > 0) {
                $_notice.remove();
            }
            $eself.prepend(notice(message, type));
            $eself.find('.dgv-loader').css({'display': 'none'});
            $eself.find('button[type=submit]').prop('disabled', false);
            //$eself.find('.dgv-progress-bar').hide();
            //updateProgressBar($eself.find('.dgv-progress-bar'), 0);
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
                var $_notice = $self.find('.wvv-notice-wrapper');
                if ($_notice.length > 0) {
                    $_notice.remove();
                }
                $self.prepend(notice(message, type));
            }
        });
        return false;
    });
})(jQuery);