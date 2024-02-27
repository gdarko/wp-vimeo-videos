/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2023 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
 *
 * This file is property of IDEOLOGIX MEDIA Dooel (https://ideologix.com)
 * This file is part of Vimeify Plugin - https://wordpress.org/plugins/vimeify/
 *
 * Vimeify - Formerly "WP Vimeo Videos" is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * Vimeify - Formerly "WP Vimeo Videos" is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this plugin. If not, see <https://www.gnu.org/licenses/>.
 *
 * Code developed by Darko Gjorgjijoski <dg@darkog.com>.
 **********************************************************************/

import "./editor.scss"
import {useBlockProps} from '@wordpress/block-editor';
import {Button, FormFileUpload, RadioControl, SelectControl, TextareaControl, TextControl} from '@wordpress/components';
import {useEffect, useState} from "@wordpress/element";
import {useDispatch} from "@wordpress/data";


const VimeifyAPICore = window['WPVimeoVideos'] ? window['WPVimeoVideos'] : null;

const filterViewPrivacyOptions = (options) => {
    const newOptions = {};
    for (let i in options) {
        if (options[i].available) {
            newOptions[i] = options[i];
        }
    }
    return newOptions;
}

const Edit = ({attributes, setAttributes}) => {

    const minSearchCharacters = 2;
    const i18n = window['VimeifyUploadBlock'] && window['VimeifyUploadBlock']['i18n'] ? window['VimeifyUploadBlock']['i18n'] : {};
    const notifyEndpoint = window['VimeifyUploadBlock'] && window['VimeifyUploadBlock']['notifyEndpoint'] ? window['VimeifyUploadBlock']['notifyEndpoint'] : '';
    const methods = window['VimeifyUploadBlock']['methods'] ? window['VimeifyUploadBlock']['methods'] : {};
    const nonce = window['VimeifyUploadBlock']['nonce'] ? window['VimeifyUploadBlock']['nonce'] : '';
    const restBase = window['VimeifyUploadBlock']['restBase'] ? window['VimeifyUploadBlock']['restBase'] : '';
    const accessToken = window['VimeifyUploadBlock']['accessToken'] ? window['VimeifyUploadBlock']['accessToken'] : '';
    const isViewPrivacyEnabled = window['VimeifyUploadBlock']['upload_form_options']['enable_view_privacy'] ? 1 : 0;
    const viewPrivacyOptions = isViewPrivacyEnabled ? filterViewPrivacyOptions(window['VimeifyUploadBlock']['upload_form_options']['privacy_view']) : [];
    const defaultViewPrivacy = Object.keys(viewPrivacyOptions).find(key => {
        return true === viewPrivacyOptions[key].default;
    })

    const isFoldersEnabled = window['VimeifyUploadBlock']['upload_form_options']['enable_folders'] ? 1 : 0;
    const defaultFolder = window['VimeifyUploadBlock']['upload_form_options']['default_folder']

    const blockProps = useBlockProps();
    const dropdownPlaceholder = {label: 'Select result...', value: ''};

    const [type, setType] = useState('');
    const [title, setTitle] = useState('');
    const [description, setDescription] = useState('');
    const [file, setFile] = useState(null);
    const [value, setValue] = useState(null);
    const [viewPrivacy, setViewPrivacy] = useState(defaultViewPrivacy);
    const [folder, setFolder] = useState(defaultFolder.uri);
    const [isUploading, setUploading] = useState(false);
    const [uploadProgress, setUploadProgress] = useState(0);

    const [folderSearch, setFolderSearch] = useState('');
    const [folderResults, setFolderResults] = useState([]);

    const [remoteSearch, setRemoteSearch] = useState('');
    const [remoteResults, setRemoteResults] = useState([]);

    const [localSearch, setLocalSearch] = useState('');
    const [localResults, setLocalResults] = useState([]);

    const { savePost } = useDispatch( 'core/editor' );

    const handleUploadSave = (event) => {

        if (!VimeifyAPICore.Uploader.validateVideo(file)) {
            alert(i18n.words.sorry + ': ' + i18n.phrases.upload_invalid_file, 'error');
            return false;
        }
        const uploader = new VimeifyAPICore.Uploader(accessToken, file, {
            'title': title,
            'description': description,
            'privacy': viewPrivacy,
            'folder': folder,
            'wp': {
                'notify_endpoint': notifyEndpoint,
            },
            'beforeStart': function () {
                setUploading(true);
                setUploadProgress(0.25);
            },
            'onProgress': function (bytesUploaded, bytesTotal) {
                setUploadProgress((bytesUploaded / bytesTotal * 100).toFixed(2));
            },
            'onSuccess': function (response, currentUpload) {
                setType('');
                setAttributes({currentValue: currentUpload.uri})
                savePost();
            },
            'onError': function (error) {
                setUploading(false);
                alert('Vimeo upload error.');
            },
            'onVideoCreateError': function (error) {
                let message = '';
                const parsedError = JSON.parse(error);
                if (parsedError.hasOwnProperty('invalid_parameters')) {
                    message = parsedError['invalid_parameters'][0]['developer_message'];
                } else {
                    message = parsedError['developer_message'];
                }
                setUploading(false);
                alert(message);
            },
            'onWPNotifyError': function (error) {
                let message = '';
                const parsedError = JSON.parse(error);
                if (parsedError.hasOwnProperty('data')) {
                    message = parsedError.data;
                } else {
                    message = 'Error notifying WordPress about the file upload.';
                }
                setUploading(target, true);
                alert(message);
            }
        });
        uploader.start();
    }

    const saveRemoteSearch = (event) => {
        setType('');
        setAttributes({currentValue: value});
    }
    const saveLocalSearch = (event) => {
        setType('');
        setAttributes({currentValue: value});
    }
    const handleClear = (event) => {
        setType('');
        setAttributes({currentValue: ''});
    }

    useEffect(() => {
        const delayDebounceFn = setTimeout(() => {
            if (remoteSearch.length > minSearchCharacters) {
                const profile = new VimeifyAPICore.Profile(accessToken);
                profile.search({
                    'page': 1,
                    'per_page': 100,
                    'query': remoteSearch,
                    'sort': 'date',
                    'direction': 'desc',
                    'onSuccess': function (response) {
                        if (response.data.length > 0) {
                            setRemoteResults(response.data);
                        }
                    },
                    'onError': function (response) {
                        console.warn('Vimeify: Unable to search remote profile.');
                        console.warn(response);
                        alert('Search error: ' + response.message)
                    }
                });
            }
        }, 800)
        return () => clearTimeout(delayDebounceFn)
    }, [remoteSearch])

    useEffect(() => {
        const delayDebounceFn = setTimeout(async () => {
            if (localSearch.length > minSearchCharacters) {
                try {
                    const response = await fetch(restBase + "vimeify/v1/videos?s=" + localSearch + '&_wpnonce=' + nonce);
                    const body = await response.json();
                    setLocalResults(body?.data);
                } catch (e) {
                    console.warn('Error searching local videos:');
                    console.warn(e);
                    alert('Search error: ' + e.message)
                }
            }
        }, 800)
        return () => clearTimeout(delayDebounceFn)
    }, [localSearch])

    useEffect(() => {
        const delayDebounceFn = setTimeout(async () => {
            if (folderSearch.length > minSearchCharacters) {
                try {
                    const response = await fetch(restBase + "vimeify/v1/folders?query=" + folderSearch + '&_wpnonce=' + nonce);
                    const body = await response.json();
                    setFolderResults([defaultFolder].concat(body?.data ? body?.data : []));
                } catch (e) {
                    console.warn('Error searching folders:');
                    console.warn(e);
                    alert('Search error: ' + e.message)
                }
            }
        }, 800)
        return () => clearTimeout(delayDebounceFn)
    }, [folderSearch])


    return (<div {...blockProps}>
            <div className={attributes.currentValue ? 'vimeify-upload-form' : ''}>
                {
                    attributes.currentValue && '' !== attributes.currentValue &&
                    <div>
                        <iframe width="auto"
                                height="400"
                                src={'https://player.vimeo.com/video/' + attributes.currentValue.replace('/videos/', '')}
                                frameBorder="0"
                                allow="autoplay; encrypted-media"
                                webkitallowfullscreen
                                mozallowfullscreen
                                allowFullScreen>
                        </iframe>
                        <hr/>
                        <div style={{textAlign: 'center'}}>
                            <Button onClick={handleClear} variant="secondary">{i18n.words.clear}</Button>
                        </div>
                    </div>
                }
                {(!attributes.currentValue || '' === attributes.currentValue) ? <>
                    <h3 className="vimeify-block-title">Vimeo</h3>
                    <div style={{marginBottom: '15px'}}>
                        <RadioControl
                            label={i18n.words.radio_title}
                            selected={type}
                            options={[{label: methods.upload, value: 'upload'}, {
                                label: methods.local,
                                value: 'local'
                            }, {label: methods.search, value: 'search'},]}
                            onChange={(value) => setType(value)}
                        />
                    </div>
                    {/* Upload video form */}
                    {type === 'upload' && <div className="vimeify-upload-form-inner">
                        <TextControl
                            label={i18n.words.title}
                            value={title}
                            onChange={(value) => setTitle(value)}
                        />
                        <TextareaControl
                            label={i18n.words.description}
                            value={description}
                            onChange={(value) => setDescription(value)}
                        />

                        {parseInt(isViewPrivacyEnabled) === 1 && <SelectControl
                            label={i18n.words.view_privacy}
                            help={i18n.phrases.view_privacy_help}
                            value={viewPrivacy}
                            options={Object.keys(viewPrivacyOptions).map((key) => {
                                return {label: viewPrivacyOptions[key].name, value: key};
                            })}
                            onChange={(newValue) => setViewPrivacy(newValue)}
                        />}
                        {parseInt(isFoldersEnabled) === 1 && <div>
                            <TextControl
                                label={i18n.words.folder}
                                placeholder={i18n.phrases.folder_placeholder}
                                value={folderSearch}
                                help={folderResults.length === 0 ? i18n.phrases.folder_help : ""}
                                onChange={(value) => setFolderSearch(value)}
                            />
                            {folderResults.length > 0 && <SelectControl
                                help={i18n.phrases.folder_help}
                                value={folder}
                                options={folderResults.map((item) => {
                                    return {label: item.name, value: item.uri};
                                })}
                                onChange={(newValue) => setFolder(newValue)}/>}
                        </div>}

                        {file && <p>Selected: {file.name}</p>}
                        <FormFileUpload
                            accept="video/*"
                            variant="secondary"
                            onChange={(event) => setFile(event.currentTarget.files[0])}
                        >
                            {file ? i18n.words.video_replace : i18n.words.video_select}
                        </FormFileUpload>

                        {isUploading && <div className="vimeify-progress">
                            <div className="vimeify-progress-value" style={{width: uploadProgress + '%'}}></div>
                        </div>}

                        {file && <div style={{marginTop: '10px'}}>
                            <Button onClick={handleUploadSave} variant="primary">{i18n.words.upload}</Button>
                        </div>}
                    </div>}

                    {/* Vimeo search/select form */}
                    {type === 'search' && <div className="vimeify-remote-search-form">
                        <TextControl
                            label={i18n.phrases.remote_search_placeholder}
                            value={remoteSearch}
                            onChange={(value) => setRemoteSearch(value)}
                        />
                        {remoteResults.length > 0 && <SelectControl
                            label={i18n.words.video_list}
                            value={value}
                            options={[dropdownPlaceholder].concat(remoteResults.map((item) => {
                                return {label: item.name, value: item.uri};
                            }))}
                            onChange={(selected) => setValue(selected)}
                        />}
                        {value && <Button onClick={saveRemoteSearch} variant="primary">{i18n.words.save}</Button>}
                    </div>}

                    {/* Local search/select form */}
                    {type === 'local' && <div className="vimeify-local-search-form">
                        <TextControl
                            label={i18n.phrases.local_search_placeholder}
                            value={localSearch}
                            onChange={(value) => setLocalSearch(value)}
                        />
                        {localResults.length > 0 && <SelectControl
                            label={i18n.words.video_list}
                            value={value}
                            options={[dropdownPlaceholder].concat(localResults.map((item) => {
                                return {label: item.name, value: item.uri};
                            }))}
                            onChange={(selected) => setValue(selected)}
                        />}
                        {value && <Button onClick={saveLocalSearch} variant="primary">{i18n.words.save}</Button>}
                    </div>}
                </> : ""}

            </div>
        </div>
    );
};
export default Edit;
