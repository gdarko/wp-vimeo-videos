// Copyright Darko Gjorgjijoski <info@codeverve.com>
// 2020. All Rights Reserved.
// This file is licensed under the GPLv2 License.
// License text available at https://opensource.org/licenses/gpl-2.0.php

wp.blocks.registerBlockType('dgv/wp-vimeo-video', {
    title: 'WP Vimeo Upload',
    icon: 'video-alt',
    category: 'common',
    attributes: {
        title: {type: 'string'},
        description: {type: 'string'},
        files: {type: 'array'},
        vimeo_id: {type: 'string'},
        method: {type: 'string'},
        size_type: {type: 'string'}, // fixed or responsive
        height: {type: 'string'},
        width: {type: 'string'},
        autoplay: {type: 'string'},
        loop: {type: 'string'},
        current_message: {type: 'string'},
        current_message_type: {type: 'string'},
        search_phrase: {type: 'string'},
        search_results: {type: 'array'},
        privacy: {type: 'string'}
    },
    edit: function (props) {

        /**
         * Update the title
         * @param event
         */
        function updateTitle(event) {
            props.setAttributes({title: event.target.value})
        }

        /**
         * Update the description
         * @param event
         */
        function updateDescription(event) {
            props.setAttributes({description: event.target.value})
        }

        /**
         * Update the view privacy
         * @param event
         */
        function updateViewPrivacy(event) {
            props.setAttributes({privacy: event.target.value});
        }

        /**
         * Update the search phrase
         * @param event
         */
        function updateSearchPhrase(event) {
            props.setAttributes({search_phrase: event.target.value})
        }

        /**
         * Update the file once selected
         * @param event
         */
        function updateFile(event) {
            props.setAttributes({files: event.target.files})
        }

        /**
         * Set the current vimeo ID on selection
         * @param event
         */
        function updateCurrentVimeoId(event) {
            var is_search = Array.isArray(props.attributes.search_results) && props.attributes.search_results.length > 0;
            if (parseInt(event.target.value) > 0) {
                var method = is_search ? 'search' : 'existing';
                props.setAttributes({vimeo_id: event.target.value, method: method});
            }
        }

        /**
         * Handle switching insert methods
         * @param event
         */
        function handleMethodChange(event) {
            if (event.target.value === 'upload') {
                props.setAttributes({
                    method: event.target.value,
                    vimeo_id: undefined,
                    search_phrase: '',
                    search_results: []
                });
            } else if (event.target.value === 'existing') {
                props.setAttributes({method: event.target.value, search_phrase: '', search_results: []});
            } else if (event.target.value === 'search') {
                props.setAttributes({method: event.target.value});
            }
        }

        /**
         * Handles video upload
         * @param event
         * @returns {boolean}
         */
        function submitVideo(event) {
            event.preventDefault();
            var target = event.target;
            var submitButton = event.target.querySelector('.submitUpload');
            var videoFile = (props.attributes.files instanceof FileList && props.attributes.files.length > 0) ? props.attributes.files[0] : null;
            if (!WPVimeoVideos.Uploader.validateVideo(videoFile)) {
                swal.fire(DGVGTB.words.sorry, DGVGTB.phrases.upload_invalid_file, 'error');
                return false;
            }
            // Init upload
            var privacy = DGVGTB.upload_form_options.enable_privacy_option && props.attributes.privacy ? props.attributes.privacy : DGVGTB.default_privacy;
            var uploader = new WPVimeoVideos.Uploader(DGVGTB.access_token, videoFile, {
                'title': props.attributes.title,
                'description': props.attributes.description,
                'privacy': privacy,
                'wp': {
                    'notify_endpoint': DGVGTB.ajax_url + '?action=dgv_store_upload&_wpnonce=' + DGVGTB.nonce,
                },
                'beforeStart': function () {
                    submitButton.disabled = true;
                    startLoading(target);
                    updateProgressBar(target, 0.25);
                },
                'onProgress': function (bytesUploaded, bytesTotal) {
                    var percentage = (bytesUploaded / bytesTotal * 100).toFixed(2);
                    updateProgressBar(target, percentage);
                },
                'onSuccess': function (response, currentUpload) {
                    var video_uri = currentUpload.uri;
                    var video_uri_parts = video_uri.split('/');
                    var video_id = video_uri_parts[video_uri_parts.length - 1];
                    DGVGTB.uploads.push({title: props.attributes.title, ID: null, vimeo_id: video_id});
                    props.setAttributes({
                        'vimeo_id': video_id,
                        'current_message_type': 'success',
                        'current_message': DGVGTB.phrases.upload_success,
                        'method': 'existing',
                    });
                    submitButton.disabled = false;
                },
                'onError': function (error) {
                    props.setAttributes({
                        'current_message_type': 'error',
                        'current_message': error
                    });
                    stopLoading(target, true);
                    submitButton.disabled = false;
                    alert('Vimeo upload error.');
                },
                'onVideoCreateError': function (error) {
                    var message = '';
                    var parsedError = JSON.parse(error);
                    if(parsedError.hasOwnProperty('invalid_parameters')) {
                        message = parsedError['invalid_parameters'][0]['developer_message'];
                    } else {
                        message = parsedError['developer_message'];
                    }
                    props.setAttributes({
                        'current_message_type': 'error',
                        'current_message': message
                    });
                    stopLoading(target, true);
                    submitButton.disabled = false;
                    alert(message);
                },
                'onWPNotifyError': function (error) {
                    var message = '';
                    var parsedError = JSON.parse(error);
                    if(parsedError.hasOwnProperty('data')) {
                        message = parsedError.data;
                    } else {
                        message = 'Error notifying WordPress about the file upload.';
                    }
                    props.setAttributes({
                        'current_message_type': 'error',
                        'current_message': message
                    });
                    stopLoading(target, true);
                    submitButton.disabled = false;
                    alert(message);
                }
            });
            uploader.start();
            return true;
        }

        /**
         * Handles search event
         * @param event
         */
        function searchAccount(event) {
            //console.log(event);
            //var target = event.target;
            //var top_parent = target.parentNode.parentNode;

            var search_phrase = props.attributes.search_phrase;
            if (search_phrase !== '') {
                var vimeoProfile = new WPVimeoVideos.Profile(DGVGTB.access_token);
                var search_results = [];
                vimeoProfile.search({
                    'page': 1,
                    'per_page': 100,
                    'query': search_phrase,
                    'sort': 'date',
                    'direction': 'desc',
                    'onSuccess': function (response) {
                        for (var i in response.data) {
                            search_results.push({title: response.data[i].name, uri: response.data[i].uri});
                        }
                        props.setAttributes({
                            'search_results': search_results,
                            'vimeo_id': '',
                        });
                    },
                    'onError': function (response) {
                        swal.fire(DGVGTB.words.sorry, DGVGTB.phrases.invalid_search_phrase, 'error');
                    }
                });
            } else {
                swal.fire(DGVGTB.words.sorry, DGVGTB.phrases.invalid_search_phrase, 'error');
            }
        }

        /**
         * Updates progress bar
         * @param form
         * @param progress
         */
        function updateProgressBar(form, progress) {
            var progressBar = form.querySelector('.dgv-progress-bar');
            var progressBarInner = form.querySelector('.dgv-progress-bar-inner');
            var progressBarValue = form.querySelector('.dgv-progress-bar-value');
            progressBar.style.display = 'block';
            progressBarInner.style.width = progress + '%';
            progressBarValue.innerHTML = progress + '%';
        }

        /**
         * Handles loader
         * @param form
         */
        function startLoading(form) {
            var loader = form.querySelector('.dgv-loader');
            loader.style.display = 'inline-block';
        }

        /**
         * Stop Loading
         * @param form
         * @param hide_progressbar
         */
        function stopLoading(form, hide_progressbar) {
            var loader = form.querySelector('.dgv-loader');
            loader.style.display = 'none';
            if(hide_progressbar) {
                var progressBar = form.querySelector('.dgv-progress-bar');
                progressBar.style.display = 'none';
            }
        }

        /**
         * Generates upload options based on array
         * @param current_vimeo_id
         * @param uploads
         * @param message
         * @returns {Array}
         */
        function getUploadsDropdown(current_vimeo_id, uploads, message) {

            var uploads_options = [];
            uploads_options.push(React.createElement("option", {
                value: -1,
                key: 'standard',
            }, message));

            var current_found = false;
            for (var i in uploads) {
                if(current_vimeo_id == uploads[i].vimeo_id) {
                    current_found = true;
                }
                uploads_options.push(React.createElement("option", {
                    value: uploads[i].vimeo_id,
                    key: uploads[i].vimeo_id,
                }, uploads[i].title));
            }

            if(current_vimeo_id && !current_found) {
                var unknown_title = '#'+current_vimeo_id + ' ' + DGVGTB.phrases.existing_not_visible_current_user;
                uploads_options.push(React.createElement("option", {
                    value: current_vimeo_id,
                    key: current_vimeo_id,
                }, unknown_title));
            }

            return uploads_options;
        }

        /**
         * Returns ID by the uri
         * @param uri
         * @returns {string}
         */
        function getIDFromURI(uri) {
            var parts = uri.split('/');
            return parts[parts.length - 1];
        }

        ///////////////////////// HANDLE THE FORM LOGIC //////////////////////////////


        // Setup the vimeo ID
        var vimeo_video_id = props.attributes.vimeo_id;

        // Setup the uploads
        var uploads = DGVGTB.uploads;

        // Render editor view
        var elements = [];

        // Is search?
        var iframe_src = false;
        var is_search = Array.isArray(props.attributes.search_results) && props.attributes.search_results.length > 0;
        var is_already_selected = !isNaN(vimeo_video_id) && vimeo_video_id > 0;

        if (is_already_selected) {
            iframe_src = 'https://player.vimeo.com/video/' + vimeo_video_id;
        }

        elements.push(React.createElement('h4', {key: 'block-title'}, DGVGTB.phrases.block_title));

        // FORM CHOICES

        var methods = [];

        methods.push(React.createElement('label', {key:'upload'},
            React.createElement('input', {
                type: "radio",
                onChange: handleMethodChange,
                value: 'upload',
                className: 'dgv-field-row',
                checked: props.attributes.method === 'upload'
            }),
            DGVGTB.methods.upload
        ));

        methods.push(React.createElement('label', {key:'existing'},
            React.createElement('input', {
                type: 'radio',
                onChange: handleMethodChange,
                //name: 'method',
                value: 'existing',
                className: 'dgv-field-row',
                checked: props.attributes.method === 'existing'
            }),
            DGVGTB.methods.local
        ))

        if(DGVGTB.enable_vimeo_search) {
            methods.push(React.createElement('label', {key:'search'},
                React.createElement('input', {
                    type: 'radio',
                    onChange: handleMethodChange,
                    value: 'search',
                    className: 'dgv-field-row',
                    checked: props.attributes.method === 'search'
                }),
                DGVGTB.methods.search
            ))
        }


        elements.push(React.createElement('div', {className: 'dgv-vimeo-form-row', key: 'options'}, methods));

        // METHOD: UPLOAD NEW
        if (props.attributes.method === 'upload') {

            elements.push(React.createElement('div', {className: 'dgv-vimeo-form-row', key: 'options--1'},
                React.createElement('input', {
                    type: 'text',
                    placeholder: DGVGTB.words.title,
                    onChange: updateTitle,
                    //name: 'title',
                    className: 'dgv-field-row',
                })
            ));

            elements.push(React.createElement('div', {className: 'dgv-vimeo-form-row', key: 'options--2'},
                React.createElement('textarea', {
                    placeholder: DGVGTB.words.desc,
                    onChange: updateDescription,
                    //name: 'description',
                    className: 'dgv-field-row',
                    columns: 50,
                    rows: 8
                })
            ));

            if(DGVGTB.upload_form_options.enable_privacy_option) {
                var privacy_options = [];
                var privacy_default = 'anybody';
                for (var key in DGVGTB.upload_form_options.privacy_view) {
                    var name = DGV.upload_form_options.privacy_view[key].name;
                    var is_available = DGVGTB.upload_form_options.privacy_view[key].available;
                    privacy_default = DGVGTB.upload_form_options.privacy_view[key].default ? key : privacy_default;
                    privacy_options.push(React.createElement("option", {
                        value: key,
                        key: key,
                        disabled: false === is_available,
                    }, name));
                }
                if(props.attributes.privacy) {
                    privacy_default = props.attributes.privacy;
                }
                elements.push(React.createElement('div', {className: 'dgv-vimeo-form-row', key: 'options--3'},
                    React.createElement('select', {
                        onChange: updateViewPrivacy,
                        className: 'dgv-field-row',
                        value: privacy_default,
                    }, privacy_options)
                ));
            }

            elements.push(React.createElement('div', {className: 'dgv-vimeo-form-row', key: 'options--4'},
                React.createElement('input', {
                    type: 'file',
                    placeholder: DGVGTB.words.file,
                    onChange: updateFile,
                    //name: 'file',
                    className: 'dgv-field-row',
                }),
            ));
            elements.push(React.createElement('div', {className: 'dgv-vimeo-form-row', key: 'options--5'},
                React.createElement('div', {
                    className: 'dgv-progress-bar',
                    style: {display: 'none'},
                    key: 'options--51'
                }, [
                    React.createElement('div', {
                            className: 'dgv-progress-bar-inner',
                            style: {width: 0 + '%'},
                            key: 'options--511'
                        },
                    ),
                    React.createElement('div', {
                            className: 'dgv-progress-bar-value',
                            key: 'options--512'
                        }, '0%'
                    )
                ]),
            ));
            elements.push(React.createElement('div', {className: 'dgv-vimeo-form-row', key: 'options--6'}, [
                    React.createElement('div', {
                            className: 'dgv-loader',
                            style: {display: 'none'},
                            key: 'options--61'
                        },
                    ),
                    React.createElement('button', {
                        type: 'submit',
                        className: 'button submitUpload',
                        'data-waiting': DGVGTB.words.uploading3d,
                        'data-finished': DGVGTB.words.upload,
                        key: 'options--62'
                    }, DGVGTB.words.upload)
                ]
            ));

            // METHOD: EXISTING
        } else if (props.attributes.method === 'existing') {

            var uploads_options = getUploadsDropdown(vimeo_video_id, uploads, 'Select existing video');
            var existing_params = {onChange: updateCurrentVimeoId, className: 'dgv-field-row', key: 'options--71'};
            var current_video_id = props.attributes.vimeo_id;
            if(current_video_id) {
                existing_params.value = current_video_id;
            }
            elements.push(React.createElement('div', {className: 'dgv-vimeo-form-row',key: 'options--7'},
                React.createElement(
                    'select',
                    existing_params,
                    uploads_options
                )
            ));

            // METHOD: SEARCH
        } else if (props.attributes.method === 'search') {

            var elements_search = [];
            elements_search.push(React.createElement('div', {className: 'dgv-vimeo-search-controls', key: 'options--8'}, [
                    React.createElement('input', {
                        type: 'text',
                        placeholder: DGVGTB.phrases.enter_phrase,
                        value: props.attributes.search_phrase,
                        onChange: updateSearchPhrase,
                        className: 'dgv-field-row dgv-search-field',
                        key: 'options--81'
                    }),
                    React.createElement('button', {
                        type: 'button',
                        onClick: searchAccount,
                        className: 'button submitSearch',
                        key: 'options--82'
                    }, DGVGTB.words.search)
                ]
            ));
            if (is_search) {
                uploads = [];
                for (var i in props.attributes.search_results) {
                    uploads.push({
                        'title': props.attributes.search_results[i].title,
                        'vimeo_id': getIDFromURI(props.attributes.search_results[i].uri),
                    })
                }
                var upload_options = getUploadsDropdown(vimeo_video_id, uploads, DGVGTB.phrases.select_video);
                elements_search.push(React.createElement(
                    'select',
                    {onChange: updateCurrentVimeoId, className: 'dgv-field-row dgv-vimeo-search-results', key: 'options--9'},
                    upload_options
                ));
            }
            elements.push(React.createElement('div', {className: 'dgv-vimeo-form-row', key: 'options--10'}, elements_search));
        }

        if (is_already_selected && iframe_src) {
            //elements.push(React.createElement('hr', {key: 'separator-1'}));
            elements.push(React.createElement('div', {'className': 'dgv-embed-container', key: 'options--9'},
                React.createElement('iframe', {
                    src: iframe_src,
                    frameBorder: '0',
                    mozallowfullscreen: 'true',
                    allowFullScreen: true,
                    key: 'options--91'
                })
            ));
        }

        return React.createElement('form', {className: 'dgv-vimeo-upload-form', onSubmit: submitVideo}, elements);
    },
    save: function (props) {
        var element;
        if (!isNaN(props.attributes.vimeo_id)) {
            var vimeo_video_id = props.attributes.vimeo_id;
            var video_width = props.attributes.width;
            var video_height = props.attributes.height;
            var size_type = props.attributes.size_type;
            if (!size_type) {
                size_type = 'responsive';
            }
            if (isNaN(video_width) || size_type === 'responsive') {
                video_width = 'auto';
            }
            if (isNaN(video_height) || size_type === 'responsive') {
                video_height = 'auto';
            }

            element = wp.element.createElement('div', {
                'className': 'dgv-embed-wrapper',
            }, '[dgv_vimeo_video id="' + vimeo_video_id + '" type="' + size_type + '" width="' + video_width + '" height="' + video_height + '" eparam1="-1" eparam2="-1" eparam3="-1"]');

        } else {
            element = wp.element.createElement(
                "div",
                {className: 'dgv-vimeo dgv-viemo-missing'},
                'No video uploaded.'
            );
        }
        return element;
    },
});