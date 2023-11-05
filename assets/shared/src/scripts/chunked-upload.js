// Copyright Darko Gjorgjijoski <info@codeverve.com>
// 2020. All Rights Reserved.
// This file is licensed under the GPLv2 License.
// License text available at https://opensource.org/licenses/gpl-2.0.php

'use strict';
(function () {

    var prefix = '';

    /**
     * All connections are slow by default.
     *
     * @type {boolean|null}
     */
    var isSlow = null;

    /**
     * Default settings for our speed test.
     *
     * @type {{maxTime: number, payloadSize: number}}
     */
    var speedTestSettings = {
        maxTime: 3000, // Max time (ms) it should take to be considered a 'fast connection'.
        payloadSize: 100 * 1024, // Payload size.
    };

    /**
     * Create a random payload for the speed test.
     *
     * @returns {string} Random payload.
     */
    function getPayload() {

        var data = '';

        for (var i = 0; i < speedTestSettings.payloadSize; ++i) {
            data += String.fromCharCode(Math.round(Math.random() * 36 + 64));
        }

        return data;
    }

    /**
     * Run speed tests and flag the clients as slow or not. If a connection
     * is slow it would let the backend know and the backend most likely
     * would disable parallel uploads and would set smaller chunk sizes.
     * @param {object} dz
     * @param {Function} next Function to call when the speed detection is done.
     */
    function speedTest(dz, next) {

        if (null !== isSlow) {
            setTimeout(next);
            return;
        }

        var data = getPayload();
        var start = new Date;

        wp.ajax.post({
            action: prefix_endpoint(dz, 'file_upload_speed_test'),
            data: data,
        }).then(function () {

            var delta = new Date - start;

            isSlow = delta >= speedTestSettings.maxTime;

            next();
        }).fail(function () {

            isSlow = true;

            next();
        });
    }

    /**
     * Toggle loading message above submit button.
     *
     * @param {object} $form jQuery form element.
     *
     * @returns {Function} event handler function.
     */
    function toggleLoadingMessage($form) {

        return function () {
            if (!$form.find('.dgv-uploading-in-progress-alert').length) {
                $form.find('.dgv-submit-container').before('<div class="dgv-error-alert dgv-uploading-in-progress-alert">' + window.DGV_CHUNKED_UPLOAD.loading_message + '</div>');
            }
        };
    }

    /**
     * Disable submit button when we are sending files to the server.
     *
     * @param {object} dz Dropzone object.
     */
    function toggleSubmit(dz) {

        var $form = jQuery(dz.element).closest('form');
        var $btn = $form.find('input[type=submit]');
        if (!$btn.length) {
            $btn = $form.find('button[type=submit]');
        }

        // Force dz.loading to be zero if it's below it, to make sure we
        // don't decrement it below zero.
        if (dz.loading < 0) {
            dz.loading = 0;
        }

        var disabled = dz.loading > 0;
        var handler = toggleLoadingMessage($form);

        if (disabled) {
            $btn.prop('disabled', true);
            if (!$form.find('.dgv-submit-overlay').length) {
                $btn.parent().addClass('dgv-submit-overlay-container');
                $btn.parent().append('<div class="dgv-submit-overlay"></div>');
                $form.find('.dgv-submit-overlay').css('width', $btn.outerWidth() + 'px');
                $form.find('.dgv-submit-overlay').css('height', $btn.parent().outerHeight() + 'px');
                $form.find('.dgv-submit-overlay').on('click', handler);
            }
        } else {
            $btn.prop('disabled', false);
            $form.find('.dgv-submit-overlay').off('click', handler);
            $form.find('.dgv-submit-overlay').remove();
            $btn.parent().removeClass('dgv-submit-overlay-container');
            if ($form.find('.dgv-uploading-in-progress-alert').length) {
                $form.find('.dgv-uploading-in-progress-alert').remove();
            }
        }
    }

    /**
     * Try to parse JSON or return false.
     *
     * @param {string} str JSON string candidate.
     *
     * @returns {*} Parse object or false.
     */
    function parseJSON(str) {
        try {
            return JSON.parse(str);
        } catch (e) {
            return false;
        }
    }

    /**
     * Leave only objects with length.
     *
     * @param {object} el Any array.
     *
     * @returns {bool} Has length more than 0 or no.
     */
    function onlyWithLength(el) {
        return el.length > 0;
    }

    /**
     * Leave only positive elements.
     *
     * @param {*} el Any element.
     *
     * @returns {*} Filter only positive.
     */
    function onlyPositive(el) {
        return el;
    }

    /**
     * Get xhr.
     *
     * @param {object} el Object with xhr property.
     *
     * @returns {*} Get XHR.
     */
    function getXHR(el) {
        return el.chunkResponse || el.xhr;
    }

    /**
     * Get response text.
     *
     * @param {object} el Xhr object.
     *
     * @returns {object} Response text.
     */
    function getResponseText(el) {
        return typeof el === 'string' ? el : el.responseText;
    }

    /**
     * Get data.
     *
     * @param {object} el Object with data property.
     *
     * @returns {object} Data.
     */
    function getData(el) {
        return el.data;
    }

    /**
     * Get value from files.
     *
     * @param {object} files Dropzone files.
     *
     * @returns {object} Prepared value.
     */
    function getValue(files) {
        return files
            .map(getXHR)
            .filter(onlyPositive)
            .map(getResponseText)
            .filter(onlyWithLength)
            .map(parseJSON)
            .filter(onlyPositive)
            .map(getData);
    }

    /**
     * Sending event higher order function.
     *
     * @param {object} dz Dropzone object.
     * @param {object} data Adding data to request.
     *
     * @returns {Function} Handler function.
     */
    function sending(dz, data) {

        return function (file, xhr, formData) {

            /*
             * We should not allow sending a file, that exceeds server post_max_size.
             * With this "hack" we redefine the default send functionality
             * to prevent only this object from sending a request at all.
             * The file that generated that error should be marked as rejected,
             * so Dropzone will silently ignore it.
             *
             * If Chunks are enabled the file size will never exceed (by a PHP constraint) the
             * postMaxSize. This block shouldn't be removed nonetheless until the "modern" upload is completely
             * deprecated and removed.
             */
            if (file.size > this.dataTransfer.postMaxSize) {
                xhr.send = function () {
                };

                file.accepted = false;
                file.processing = false;
                file.status = 'rejected';
                file.previewElement.classList.add('dz-error');
                file.previewElement.classList.add('dz-complete');

                return;
            }

            Object.keys(data).forEach(function (key) {
                formData.append(key, data[key]);
            });
        };
    }

    /**
     * Convert files to input value.
     *
     * @param {object} files Files list.
     *
     * @returns {string} Converted value.
     */
    function convertFilesToValue(files) {

        return files.length ? JSON.stringify(files) : '';
    }

    /**
     * Update value in input.
     *
     * @since 1.5.6
     *
     * @param {object} dz Dropzone object.
     */
    function updateInputValue(dz) {

        var $input = jQuery(dz.element).closest('.dgv-chunked-uploader-wrapper').find('input[name=' + dz.dataTransfer.name + ']');

        $input.val(convertFilesToValue(getValue(dz.files))).trigger('input');

        if (typeof jQuery.fn.valid !== 'undefined') {
            $input.valid();
        }
    }

    /**
     * Complete event higher order function.
     *
     * @deprecated 1.6.2
     *
     * @since 1.5.6
     *
     * @param {object} dz Dropzone object.
     *
     * @returns {Function} Handler function.
     */
    function complete(dz) {

        return function () {
            dz.loading = dz.loading || 0;
            dz.loading--;
            toggleSubmit(dz);
            updateInputValue(dz);
        };
    }

    /**
     * Add an error message to the current file.
     *
     * @since 1.6.2
     *
     * @param {object} file         File object.
     * @param {string} errorMessage Error message
     */
    function addErrorMessage(file, errorMessage) {

        if (file.isErrorNotUploadedDisplayed) {
            return;
        }

        var span = document.createElement('span');
        span.innerText = errorMessage.toString();
        span.setAttribute('data-dz-errormessage', '');

        file.previewElement.querySelector('.dz-error-message').appendChild(span);
    }

    /**
     * Confirm the upload to the server.
     *
     * The confirmation is needed in order to let PHP know
     * that all the chunks have been uploaded.
     *
     * @since 1.6.2
     *
     * @param {object} dz Dropzone object.
     *
     * @returns {Function} Handler function.
     */
    function confirmChunksFinishUpload(dz) {

        return function confirm(file) {

            if (!file.retries) {
                file.retries = 0;
            }

            if ('error' === file.status) {
                return;
            }

            /**
             * Retry finalize function.
             *
             * @since 1.6.2
             */
            function retry() {
                file.retries++;

                if (file.retries === 3) {
                    addErrorMessage(file, window.DGV_CHUNKED_UPLOAD.errors.file_not_uploaded);
                    return;
                }

                setTimeout(function () {
                    confirm(file);
                }, 5000 * file.retries);
            }

            /**
             * Fail handler for ajax request.
             *
             * @since 1.6.2
             *
             * @param {object} response Response from the server
             */
            function fail(response) {

                var hasSpecificError = response.responseJSON &&
                    response.responseJSON.success === false &&
                    response.responseJSON.data;

                if (hasSpecificError) {
                    addErrorMessage(file, response.responseJSON.data);
                } else {
                    retry();
                }
            }

            /**
             * Handler for ajax request.
             *
             * @since 1.6.2
             *
             * @param {object} response Response from the server
             */
            function complete(response) {

                file.chunkResponse = JSON.stringify({data: response});
                dz.loading = dz.loading || 0;
                dz.loading--;

                toggleSubmit(dz);
                updateInputValue(dz);
            }

            wp.ajax.post(jQuery.extend(
                {
                    action: prefix_endpoint(dz, 'file_chunks_uploaded'),
                    key: dz.dataTransfer.key,
                    name: file.name,
                },
                dz.options.params.call(dz, null, null, {file: file, index: 0})
            )).then(complete).fail(fail);

            // Move to upload the next file, if any.
            dz.processQueue();
        };
    }

    /**
     * Toggle showing empty message.
     *
     * @since 1.5.6
     *
     * @param {object} dz Dropzone object.
     */
    function toggleMessage(dz) {

        setTimeout(function () {
            var validFiles = dz.files.filter(function (file) {
                return file.accepted;
            });

            if (validFiles.length >= dz.options.maxFiles) {
                dz.element.querySelector('.dz-message').classList.add('hide');
            } else {
                dz.element.querySelector('.dz-message').classList.remove('hide');
            }
        }, 0);
    }

    /**
     * Toggle error message if total size more than limit.
     * Runs for each file.
     *
     * @since 1.5.6
     *
     * @param {object} file Current file.
     * @param {object} dz   Dropzone object.
     */
    function validatePostMaxSizeError(file, dz) {

        setTimeout(function () {
            if (file.size >= dz.dataTransfer.postMaxSize) {
                var errorMessage = window.DGV_CHUNKED_UPLOAD.errors.post_max_size;
                if (!file.isErrorNotUploadedDisplayed) {
                    file.isErrorNotUploadedDisplayed = true;
                    errorMessage = window.DGV_CHUNKED_UPLOAD.errors.file_not_uploaded + ' ' + errorMessage;
                    addErrorMessage(file, errorMessage);
                }
            }
        }, 1);
    }

    /**
     * Start File Upload.
     *
     * This would do the initial request to start a file upload. No chunk
     * is uploaded at this stage, instead all the information related to the
     * file are send to the server waiting for an authorization.
     *
     * If the server authorizes the client would start uploading the chunks.
     *
     * @since 1.6.2
     *
     * @param {object} dz   Dropzone object.
     * @param {object} file Current file.
     */
    function initFileUpload(dz, file) {

        wp.ajax.post(jQuery.extend(
            {
                action: prefix_endpoint(dz, 'upload_chunk_init'),
                key: dz.dataTransfer.key,
                name: file.name,
                slow: isSlow,
            },
            dz.options.params.call(dz, null, null, {file: file, index: 0})
        )).then(function (response) {

            // File upload has been authorized.

            for (var key in response) {
                dz.options[key] = response[key];
            }

            if (response.dzchunksize) {
                dz.options.chunkSize = parseInt(response.dzchunksize, 10);
                file.upload.totalChunkCount = Math.ceil(file.size / dz.options.chunkSize);
            }

            dz.processQueue();
        }).fail(function (response) {

            file.status = 'error';

            addErrorMessage(file, response);

            dz.processQueue();
        });
    }

    /**
     * Validate the file when it was added in the dropzone.
     *
     * @since 1.5.6
     *
     * @param {object} dz Dropzone object.
     *
     * @returns {Function} Handler function.
     */
    function addedFile(dz) {

        return function (file) {

            if (file.size >= dz.dataTransfer.postMaxSize) {
                validatePostMaxSizeError(file, dz);
            } else {
                speedTest(dz, function () {
                    initFileUpload(dz, file);
                });
            }

            dz.loading = dz.loading || 0;
            dz.loading++;
            toggleSubmit(dz);

            toggleMessage(dz);
        };
    }

    /**
     * Send an AJAX request to remove file from the server.
     *
     * @since 1.5.6
     *
     * @param {string} file File name.
     * @param {object} dz Dropzone object.
     */
    function removeFromServer(file, dz) {

        wp.ajax.post({
            action: prefix_endpoint(dz, 'remove_file'),
            file: file,
            key: dz.dataTransfer.key,
        });
    }

    /**
     * Init the file removal on server when user removed it on front-end.
     *
     * @since 1.5.6
     *
     * @param {object} dz Dropzone object.
     *
     * @returns {Function} Handler function.
     */
    function removedFile(dz) {

        return function (file) {
            toggleMessage(dz);

            var json = file.chunkResponse || (file.xhr || {}).responseText;

            if (json) {
                var object = parseJSON(json);

                if (object && object.data && object.data.file) {
                    removeFromServer(object.data.file, dz);
                }
            } else if(file.hasOwnProperty('server_file') && file.server_file) {
                removeFromServer(file.server_file, dz);
            }

            updateInputValue(dz);

            dz.loading = dz.loading || 0;
            dz.loading--;
            toggleSubmit(dz);
        };
    }

    /**
     * Process any error that was fired per each file.
     * There might be several errors per file, in that case - display "not uploaded" text only once.
     *
     * @since 1.5.6.1
     *
     * @param {object} dz Dropzone object.
     *
     * @returns {Function} Handler function.
     */
    function error(dz) {

        return function (file, errorMessage) {

            if (file.isErrorNotUploadedDisplayed) {
                return;
            }

            file.isErrorNotUploadedDisplayed = true;
            file.previewElement.querySelectorAll('[data-dz-errormessage]')[0].textContent = window.DGV_CHUNKED_UPLOAD.errors.file_not_uploaded + ' ' + errorMessage;
        };
    }

    /**
     * Dropzone.js init for each field.
     *
     * @since 1.5.6
     *
     * @param {object} $el
     *
     * @returns {object} Dropzone object.
     */
    function dropZoneInit($el) {

        var fieldKey = $el.dataset.fieldKey;
        var maxFiles = parseInt($el.dataset.maxFileNumber, 10);

        var acceptedFiles = $el.dataset.extensions.split(',').map(function (el) {
            return '.' + el;
        }).join(',');

        // Configure and modify Dropzone library.
        var dz = new window.Dropzone($el, {
            url: window.DGV_CHUNKED_UPLOAD.url,
            addRemoveLinks: true,
            chunking: true,
            forceChunking: true,
            retryChunks: true,
            chunkSize: parseInt($el.dataset.fileChunkSize, 10),
            paramName: $el.dataset.inputName,
            parallelChunkUploads: !!($el.dataset.parallelUploads || '').match(/^true$/i),
            parallelUploads: parseInt($el.dataset.maxParallelUploads, 10),
            autoProcessQueue: false,
            maxFilesize: (parseInt($el.dataset.maxSize, 10) / (1024 * 1024)).toFixed(2),
            maxFiles: maxFiles,
            acceptedFiles: acceptedFiles,
            dictMaxFilesExceeded: window.DGV_CHUNKED_UPLOAD.errors.file_limit.replace('{fileLimit}', maxFiles),
            dictInvalidFileType: window.DGV_CHUNKED_UPLOAD.errors.file_extension,
            dictFileTooBig: window.DGV_CHUNKED_UPLOAD.errors.file_size,
        });

        // Custom variables.
        dz.dataTransfer = {
            key: fieldKey,
            postMaxSize: $el.dataset.maxSize,
            name: $el.dataset.inputName,
        };

        // Load existing value.
        if ($el.nextElementSibling) {
            try {
                var existingFiles = JSON.parse($el.nextElementSibling.value);
                for (var i in existingFiles) {
                    var file = {name: existingFiles[i].file_user_name, size: existingFiles[i].size, server_file: existingFiles[i].file};
                    dz.emit("addedfile", file);
                    dz.emit("complete", file);
                }
            } catch (e) {
            }

        }

        // Process events.
        dz.on('sending', sending(dz, {
            action: prefix_endpoint(dz, 'upload_chunk'),
            key: fieldKey,
        }));
        dz.on('addedfile', addedFile(dz));
        dz.on('removedfile', removedFile(dz));
        dz.on('complete', confirmChunksFinishUpload(dz));
        dz.on('error', error(dz));

        return dz;
    }

    /**
     *
     * @param {Dropzone} dz
     * @param action
     */
    function prefix_endpoint(dz, action) {
        return dz.element.dataset.handler + '_' + action;
    }

    /**
     * DOMContentLoaded handler.
     *
     * @since 1.5.6
     */
    function ready() {
        window.dgv_chunked_upload_fields = window.dgv_chunked_upload_fields || {};
        window.dgv_chunked_upload_fields.dropzones = [].slice.call(document.querySelectorAll('.dgv-chunked-uploader')).map(dropZoneInit);
    }

    /**
     * File uploader init.
     * @type {{init: dgvChunkedFileUpload.init}}
     */
    var dgvChunkedFileUpload = {

        init: function () {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', ready);
            } else {
                ready();
            }
        },
    };

    // Initialize.
    dgvChunkedFileUpload.init();
    window.dgvChunkedFileUpload = dgvChunkedFileUpload;
}());
