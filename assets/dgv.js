(function ($) {

    var notice = function (message, type) {
        return '<div id="dg-notice" class="form-row"><div class="notice notice-' + type + ' is-dismissible dgv-clear-padding"><p>' + message + '</p></div></div>\n';
    };
    // Handle vimeo upload
    $('#dg-vimeo-upload').submit(function (e) {
        var $self = $(this);
        // Setup form data
        var $file_input = $(this).find('#vimeo_video');
        var submittedData = $(this).serializeArray();
        var formData = new FormData();
        for (var i in submittedData) {
            if (submittedData.hasOwnProperty(i)) {
                formData.append(submittedData[i].name, submittedData[i].value);
            }
        }
        formData.append('action', 'dgv_handle_upload');
        formData.append('nonce', DGV.nonce);
        formData.append('file', $file_input[0].files[0]);
        $.ajax({
            url: DGV.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                var $_notice = $self.find('#dg-notice');
                if ($_notice.length > 0) {
                    $_notice.remove();
                }
                $self.prepend(notice(DGV.uploading, 'info'));
            },
            success: function (response) {
                var message;
                var type;
                if (response.success) {
                    message = response.data.message;
                    type = 'success';
                } else {
                    message = response.data.message;
                    type = 'error';
                }
                var $_notice = $self.find('#dg-notice');
                if ($_notice.length > 0) {
                    $_notice.remove();
                }
                $self.prepend(notice(message, type));
            },
            error: function (request, status, error) {
                var message = request.responseText;
                var type = 'error';
                var $_notice = $self.find('#dg-notice');
                if ($_notice.length > 0) {
                    $_notice.remove();
                }
                $self.prepend(notice(message, type));
            }
        });
        return false;
    });

    // Handle vimeo settings
    $('#dg-vimeo-settings').submit(function (e) {
        var $self = $(this);
        var data = $self.serialize();
        $.ajax({
            url: DGV.ajax_url + '?action=dgv_handle_settings&nonce=' + DGV.nonce,
            type: 'POST',
            data: data,
            success: function (response) {
                var message;
                var type;
                if (response.success) {
                    message = response.data.message;
                    type = 'success';
                } else {
                    message = response.data.message;
                    type = 'error';
                }
                var $_notice = $self.find('#dg-notice');
                if ($_notice.length > 0) {
                    $_notice.remove();
                }
                $self.prepend(notice(message, type));
            }
        });
        return false;
    });
})(jQuery);