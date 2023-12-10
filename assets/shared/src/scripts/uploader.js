// Copyright Darko Gjorgjijoski <info@codeverve.com>
// 2020. All Rights Reserved.
// This file is licensed under the GPLv2 License.
// License text available at https://opensource.org/licenses/gpl-2.0.php
if (!window.hasOwnProperty('WPVimeoVideos')) {
    window['WPVimeoVideos'] = {};
}

/**
 * Uploader
 * @param accessToken
 * @param file {File}
 * @param params
 * @constructor
 */
window['WPVimeoVideos'].Uploader = function (accessToken, file, params) {

    /**
     * The access token.
     */
    this.accessToken = accessToken;

    /**
     * Acceppt header
     * @type {string}
     */
    this.accept = 'application/vnd.vimeo.*+json;version=3.4';

    /**
     * The chunk size
     * @type {number}
     */
    this.chunkSize = 5000000;

    /**
     * The file object
     * @type {File}
     */
    this.file = file;

    /**
     * The params object
     */
    this.params = params;

    /**
     * The vimeo endpoints
     * @type {{upload: string}}
     */
    this.endpoints = {
        create_video: 'https://api.vimeo.com/me/videos',
        delete_video: 'https://api.vimeo.com/',
        me: 'https://api.vimeo.com/me/videos',
    };

    /**
     * The tus uploader instance
     * @type {null}
     */
    this.currentTusUploader = null;

    /**
     * The current resource
     */
    this.currentUpload = null;
};


/**
 * Convert object
 * @param obj
 * @returns {string|string}
 */
/*WPVimeoVideos.Uploader.serializeObject = function (obj) {
    var str = "";
    for (var key in obj) {
        if (str != "") {
            str += "&";
        }
        str += key + "=" + encodeURIComponent(obj[key]);
    }
    return str;
};*/
WPVimeoVideos.Uploader.serializeObject = function (obj, key, list) {
    list = list || [];
    if (typeof (obj) == 'object') {
        for (var idx in obj)
            WPVimeoVideos.Uploader.serializeObject(obj[idx], key ? key + '[' + idx + ']' : idx, list);
    } else {
        list.push(key + '=' + encodeURIComponent(obj));
    }
    return list.join('&');
}

/**
 * Check if the file is valid video file.
 * @param file {File}
 */
WPVimeoVideos.Uploader.validateVideo = function (file) {

    console.log(file);

    if (file instanceof File) {
        if (file.size <= 0) {
            return false;
        } else if (file.type.indexOf('video/') === -1) {
            return false;
        } else {
            return true;
        }
    } else {
        console.log('Fails 3');
        return false;
    }
};

/**
 * Create Video
 */
WPVimeoVideos.Uploader.prototype.start = function () {

    var self = this;
    var http = new XMLHttpRequest();

    var requestData = {
        name: self.params.title,
        description: self.params.description,
        upload: {
            approach: 'tus',
            size: self.file.size,
        },
    };

    if (self.params.hasOwnProperty('privacy') && self.params !== 'default') {
        requestData.privacy = {view: self.params.privacy};
    }

    if (self.params.hasOwnProperty('folder') && (self.params.folder && self.params.folder !== 'default')) {
        requestData.folder_uri = self.params.folder;
    }

    var requestBody = JSON.stringify(requestData);

    http.open('POST', self.endpoints.create_video, true);
    http.setRequestHeader('Authorization', 'bearer ' + this.accessToken);
    http.setRequestHeader('Content-Type', 'application/json');
    http.setRequestHeader('Accept', this.accept);

    http.onreadystatechange = function () {
        if (http.readyState === 4) {
            var responseText = http.responseText;
            if (http.status === 200) { // OK
                var response = JSON.parse(responseText);
                var upload_link = response.upload.upload_link;
                self.uploadToVimeo(upload_link);
                self.currentUpload = response;
            } else {
                if (self.params.hasOwnProperty('onVideoCreateError')) {
                    self.params.onVideoCreateError(responseText);
                }
            }
        }
    };
    if (self.params.hasOwnProperty('beforeStart')) {
        self.params.beforeStart();
    }
    http.send(requestBody);

};

/**
 * Abort video upload process
 */
WPVimeoVideos.Uploader.prototype.abort = function (onSuccess, onError) {
    if (this.currentTusUploader) {
        var self = this;
        this.currentTusUploader.abort(true, function (e) {
            var http = new XMLHttpRequest();
            var endpoint = self.endpoints.delete_video + self.currentUpload.uri;
            http.open('DELETE', endpoint, true);
            http.setRequestHeader('Authorization', 'bearer ' + self.accessToken);
            http.setRequestHeader('Content-Type', 'application/json');
            http.setRequestHeader('Accept', self.accept);
            http.onreadystatechange = function () {
                if (http.readyState === XMLHttpRequest.DONE) {
                    if (http.status > 200 && http.status < 210) { // OK
                        if (onSuccess) {
                            onSuccess(http.status, http.responseText);
                        }
                    } else if (http.status > 400 && http.status < 500) {
                        if (onError) {
                            onError(http.status, http.responseText);
                        }
                    }
                }
            };
            http.send();
        })
    }
};

/**
 * Upload video to vimeo
 * @param uploadUrl
 */
WPVimeoVideos.Uploader.prototype.uploadToVimeo = function (uploadUrl) {
    var self = this;
    if (!this.file) {
        return;
    }
    var options = {
        endpoint: uploadUrl,
        uploadUrl: uploadUrl,
        chunkSize: self.chunkSize,
        retryDelays: [0, 1000, 3000, 5000],
        metadata: {
            filename: self.file.name,
            filetype: self.file.type
        },
        headers: {
            'Accept': self.accept,
        }
    };
    if (self.params.hasOwnProperty('onError')) {
        options.onError = function (error) {
            self.params.onError(error);
        }
    }
    if (self.params.hasOwnProperty('onProgress')) {
        options.onProgress = function (bytesUploaded, bytesTotal) {
            self.params.onProgress(bytesUploaded, bytesTotal);
        }
    }
    options.onSuccess = function () {
        self.notifyWP(function (response) {
            if (self.params.hasOwnProperty('onSuccess')) {
                self.params.onSuccess(response, self.currentUpload);
            }
        });
    };
    this.currentTusUploader = new tus.Upload(self.file, options);
    this.currentTusUploader.start();
};
/**
 * Used to notify WordPress abount completed upload.
 * @param callback
 */
WPVimeoVideos.Uploader.prototype.notifyWP = function (callback) {

    var self = this;
    // Skip the notify call if no valid data provided.
    if (!self.params.hasOwnProperty('wp') || (self.params.hasOwnProperty('wp') && !self.params.wp.hasOwnProperty('notify_endpoint')) || false === self.params.wp.notify_endpoint) {
        if (false === self.params.wp.notify_endpoint) {
            console.log('Notify endpoint disabled.');
        } else {
            console.log('Not valid notify_endpoint specified.');
        }
        callback(null);
        return;
    }
    // Create request
    var http = new XMLHttpRequest();
    http.open('POST', self.params.wp.notify_endpoint, true);
    http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;');
    http.onreadystatechange = function () {
        if (http.readyState === 4) {
            var responseText = http.responseText;
            if (http.status === 200) { // OK
                var response = JSON.parse(responseText);
                callback(response);
            } else {
                if (self.params.hasOwnProperty('onWPNotifyError')) {
                    self.params.onWPNotifyError(responseText);
                }
            }
        }
    };
    // Collect data
    let entry = {
        title: self.params.title,
        description: self.params.description,
        uri: self.currentUpload.uri,
        size: self.file.size,
        meta: self.params.wp.hasOwnProperty('notify_meta') ? self.params.wp.notify_meta : null,
    }
    if (self.params.hasOwnProperty('folder') && self.params.folder && self.params.folder !== 'default') {
        entry.folder_uri = self.params.folder;
    }
    if (self.params.hasOwnProperty('privacy') && self.params.privacy && self.params.privacy !== 'default') {
        entry.view_privacy = self.params.privacy;
    }

    http.send(WPVimeoVideos.Uploader.serializeObject(entry));
};


/**
 * Uploader
 * @param accessToken
 * @constructor
 */
WPVimeoVideos.Profile = function (accessToken) {

    /**
     * The access token.
     */
    this.accessToken = accessToken;

    /**
     * Acceppt header
     * @type {string}
     */
    this.accept = 'application/vnd.vimeo.*+json;version=3.4';

    /**
     * The vimeo endpoints
     * @type {{upload: string}}
     */
    this.endpoints = {
        search: 'https://api.vimeo.com/me/videos',
    };
};

/**
 * Search for video
 * @param params
 */
WPVimeoVideos.Profile.prototype.search = function (params) {
    var self = this;
    var http = new XMLHttpRequest();
    var requestParams = {};
    for (var i in params) {
        if (i === 'onSuccess' || i === 'onError') {
            continue;
        }
        requestParams[i] = params[i];
    }

    requestParams = WPVimeoVideos.Uploader.serializeObject(requestParams);

    http.open('GET', self.endpoints.search + '?' + requestParams, true);
    http.setRequestHeader('Authorization', 'bearer ' + this.accessToken);
    http.setRequestHeader('Content-Type', 'application/json');
    http.setRequestHeader('Accept', this.accept);

    http.onreadystatechange = function () {
        if (http.readyState === 4) {
            var responseText = http.responseText;
            var response = JSON.parse(responseText);
            if (http.status === 200) { // OK
                if (params.hasOwnProperty('onSuccess')) {
                    params.onSuccess(response);
                }
            } else {
                if (params.hasOwnProperty('onError')) {
                    params.onError(response);
                }
            }
        }
    };
    http.send();
};