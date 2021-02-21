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
    },
    edit: function (props) {

        function updateTitle(event) {
            props.setAttributes({title: event.target.value})
        }

        function updateDescription(event) {
            props.setAttributes({description: event.target.value})
        }

        function updateFile(event) {
            props.setAttributes({files: event.target.files})
        }

        function updateCurrentVimeoId(event) {
            if (parseInt(event.target.value) > 0) {
                props.setAttributes({vimeo_id: event.target.value, method: 'existing'});
            }
        }

        function handleFormFields(event) {
            if (event.target.value === 'upload') {
                props.setAttributes({method: event.target.value, vimeo_id: undefined});
            } else {
                props.setAttributes({method: event.target.value});
            }
        }

        function updateProgressBar(form, progress) {
            var progressBar = form.querySelector('.dgv-progress-bar');
            var progressBarInner = form.querySelector('.dgv-progress-bar-inner');
            var progressBarValue = form.querySelector('.dgv-progress-bar-value');
            progressBar.style.display = 'block';
            progressBarInner.style.width = progress + '%';
            progressBarValue.innerHTML = progress + '%';
        }

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

        function submitVideo(event) {
            event.preventDefault();

            var target = event.target;
            var submitButton = event.target.querySelector('.submitUpload');
            var videoFile = (props.attributes.files instanceof FileList && props.attributes.files.length > 0)  ? props.attributes.files[0] : null;
            if (!WPVimeoVideos.Uploader.validateVideo(videoFile)) {
                swal.fire(DGV.sorry, DGV.upload_invalid_file, 'error');
                return false;
            }

            // Init upload
            var uploader = new WPVimeoVideos.Uploader(DGV.access_token, videoFile, {
                'title': props.attributes.title,
                'description': props.attributes.description,
                'privacy': DGV.default_privacy,
                'wp': {
                    'notify_endpoint': DGV.ajax_url + '?action=dgv_store_upload&_wpnonce=' + DGV.nonce,
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
                    DGV.uploads.push({title: props.attributes.title, ID: null, vimeo_id: video_id});
                    props.setAttributes({
                        'vimeo_id': video_id,
                        'current_message_type': 'success',
                        'current_message': 'Video uploaded successfully!',
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

        // Setup the vimeo ID
        var vimeo_video_id = props.attributes.vimeo_id;

        // Setup the uploads
        var uploads = DGV.uploads;
        var uploads_options = [];
        uploads_options.push(React.createElement("option", {
            key:'standard-1',
            value: -1,
            disabled: true
        }, 'Select existing video'));
        for (var i in uploads) {
            uploads_options.push(React.createElement("option", {
                key: uploads[i].vimeo_id,
                value: uploads[i].vimeo_id,
            }, uploads[i].title));
        }

        // Render editor view
        var elements = [];
        if (!isNaN(vimeo_video_id)) {
            var iframe_src = 'https://player.vimeo.com/video/' + vimeo_video_id;
            elements.push(React.createElement('h4', {key:'heading-1'}, 'Edit Vimeo'));
            elements.push(React.createElement('div', {key:'option-1', className: 'dgv-vimeo-form-row'},
                React.createElement('label', {key:'label-1'},
                    React.createElement('input', {
                        key:'field-1',
                        type: 'radio',
                        onChange: handleFormFields,
                        // name: 'method',
                        value: 'existing',
                        className: 'dgv-field-row',
                        checked: props.attributes.method === 'existing'
                    }),
                    'Insert existing vimeo video'
                ),
                React.createElement('label', {key:'label-2'},
                    React.createElement('input', {
                        key:'field-2',
                        type: 'radio',
                        onChange: handleFormFields,
                        // name: 'method',
                        value: 'upload',
                        className: 'dgv-field-row',
                        checked: props.attributes.method === 'upload'
                    }),
                    'Upload new vimeo video'
                )
            ));

            var existing_params = {onChange: updateCurrentVimeoId, className: 'dgv-field-row', key: 'options--71'};
            var current_video_id = props.attributes.vimeo_id;
            if(current_video_id) {
                existing_params.value = current_video_id;
            }
            elements.push(React.createElement('div', {key:'option-2',className: 'dgv-vimeo-form-row'},
                React.createElement(
                    'select',
                    existing_params,
                    uploads_options
                )
            ));
            //elements.push(React.createElement('hr', {key:'separator-1'}));
            elements.push(React.createElement('div', {key:'preview-1','className': 'dgv-embed-container'},
                React.createElement('iframe', {
                    key:'iframe-1',
                    'src': iframe_src,
                    frameBorder: '0',
                    mozallowfullscreen: 'true',
                    allowFullScreen: true,
                })
            ));
            //elements.push(React.createElement('button', {onClick: deleteVideo, 'class': 'button'}, 'Delete'))

            return React.createElement('div', {  key:'container-1', 'className': 'dgv-vimeo-upload-form'}, elements);
        } else { // Render the editor view (when nothing is uploaded)

            elements.push(React.createElement('h4', {key:'heading-2'}, 'Upload Vimeo'));
            elements.push(React.createElement('div', {key:'option-3',className: 'dgv-vimeo-form-row'},
                React.createElement('label', {},
                    React.createElement('input', {
                        key:'field-8',
                        type: 'radio',
                        onChange: handleFormFields,
                        //name: 'method',
                        value: 'existing',
                        className: 'dgv-field-row',
                        checked: props.attributes.method === 'existing'
                    }),
                    'Insert existing vimeo video'
                ),
                React.createElement('label', {key:'label-3'},
                    React.createElement('input', {
                        key:'field-4',
                        type: 'radio',
                        onChange: handleFormFields,
                        //name: 'method',
                        value: 'upload',
                        className: 'dgv-field-row',
                        checked: props.attributes.method === 'upload'
                    }),
                    'Upload new vimeo video'
                )
            ));
            // If the method is Upload
            if (props.attributes.method === 'upload') {
                elements.push(React.createElement('div', {key:'option-4',className: 'dgv-vimeo-form-row'},
                    React.createElement('input', {
                        key:'field-5',
                        type: 'text',
                        placeholder: 'Title',
                        onChange: updateTitle,
                        //name: 'title',
                        className: 'dgv-field-row',
                    })
                ));
                elements.push(React.createElement('div', {key:'option-5',className: 'dgv-vimeo-form-row'},
                    React.createElement('textarea', {
                        key:'field-6',
                        placeholder: 'Description',
                        onChange: updateDescription,
                        //name: 'description',
                        className: 'dgv-field-row',
                        columns: 50,
                        rows: 8
                    })
                ));
                elements.push(React.createElement('div', {key:'option-6',className: 'dgv-vimeo-form-row'},
                    React.createElement('input', {
                        key:'field-7',
                        type: 'file',
                        placeholder: 'File',
                        onChange: updateFile,
                        //name: 'file',
                        className: 'dgv-field-row',
                    }),
                ));
                elements.push(React.createElement('div', {key:'option-7',className: 'dgv-vimeo-form-row'},
                    React.createElement('div', {
                        key:'progressbar',
                        className: 'dgv-progress-bar',
                        style: {display: 'none'}
                    }, [
                        React.createElement('div', {
                            key:'progressbar-inner',
                            className: 'dgv-progress-bar-inner',
                                style: {width: 0 + '%'}
                            },
                        ),
                        React.createElement('div', {
                            key:'progressbar-inner-value',
                            className: 'dgv-progress-bar-value',
                            }, '0%'
                        )
                    ]),
                ));
            } else {
                elements.push(React.createElement('div', {key:'option-12',className: 'dgv-vimeo-form-row'},
                    React.createElement(
                        'select',
                        { key:'field-8',onChange: updateCurrentVimeoId, className: 'dgv-field-row'},
                        uploads_options
                    )
                ));
            }
            elements.push(React.createElement('div', {key:'option-13',className: 'dgv-vimeo-form-row'}, [
                    React.createElement('div', {
                            key:'loader-1',
                            className: 'dgv-loader',
                            style: {display: 'none'}
                        },
                    ),
                    React.createElement('button', {
                        key:'button-1',
                        type: 'submit',
                        className: 'button submitUpload',
                        'data-waiting': 'Sending...',
                        'data-finished': 'Upload'
                    }, "Upload")
                ]
            ));
            return React.createElement('form', { key:'form-8',className: 'dgv-vimeo-upload-form', onSubmit: submitVideo}, elements);
        }
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
    }
});