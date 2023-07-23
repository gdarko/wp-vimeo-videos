/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/upload/edit.js":
/*!****************************!*\
  !*** ./src/upload/edit.js ***!
  \****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./editor.scss */ "./src/upload/editor.scss");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_4__);

/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2023 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
 *
 * This file is property of IDEOLOGIX MEDIA Dooel (https://ideologix.com)
 * This file is part of Vimeify Plugin - https://wordpress.org/plugins/wp-vimeo-videos/
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






const VimeifyAPICore = window['WPVimeoVideos'] ? window['WPVimeoVideos'] : null;
const filterViewPrivacyOptions = options => {
  const newOptions = {};
  for (let i in options) {
    if (options[i].available) {
      newOptions[i] = options[i];
    }
  }
  return newOptions;
};
const Edit = ({
  attributes,
  setAttributes
}) => {
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
  });
  const isFoldersEnabled = window['VimeifyUploadBlock']['upload_form_options']['enable_folders'] ? 1 : 0;
  const defaultFolder = window['VimeifyUploadBlock']['upload_form_options']['default_folder'];
  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.useBlockProps)();
  const dropdownPlaceholder = {
    label: 'Select result...',
    value: ''
  };
  const [type, setType] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('');
  const [title, setTitle] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('');
  const [description, setDescription] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('');
  const [file, setFile] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(null);
  const [value, setValue] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(null);
  const [viewPrivacy, setViewPrivacy] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(defaultViewPrivacy);
  const [folder, setFolder] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(defaultFolder.uri);
  const [isUploading, setUploading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const [uploadProgress, setUploadProgress] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(0);
  const [folderSearch, setFolderSearch] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('');
  const [folderResults, setFolderResults] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)([]);
  const [remoteSearch, setRemoteSearch] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('');
  const [remoteResults, setRemoteResults] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)([]);
  const [localSearch, setLocalSearch] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('');
  const [localResults, setLocalResults] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)([]);
  const {
    savePost
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_4__.useDispatch)('core/editor');
  const handleUploadSave = event => {
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
        'notify_endpoint': notifyEndpoint
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
        setAttributes({
          currentValue: currentUpload.uri
        });
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
  };
  const saveRemoteSearch = event => {
    setType('');
    setAttributes({
      currentValue: value
    });
  };
  const saveLocalSearch = event => {
    setType('');
    setAttributes({
      currentValue: value
    });
  };
  const handleClear = event => {
    setType('');
    setAttributes({
      currentValue: ''
    });
  };
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
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
            alert('Search error: ' + response.message);
          }
        });
      }
    }, 800);
    return () => clearTimeout(delayDebounceFn);
  }, [remoteSearch]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    const delayDebounceFn = setTimeout(async () => {
      if (localSearch.length > minSearchCharacters) {
        try {
          const response = await fetch(restBase + "vimeify/v1/videos?s=" + localSearch + '&_wpnonce=' + nonce);
          const body = await response.json();
          setLocalResults(body?.data);
        } catch (e) {
          console.warn('Error searching local videos:');
          console.warn(e);
          alert('Search error: ' + e.message);
        }
      }
    }, 800);
    return () => clearTimeout(delayDebounceFn);
  }, [localSearch]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    const delayDebounceFn = setTimeout(async () => {
      if (folderSearch.length > minSearchCharacters) {
        try {
          const response = await fetch(restBase + "vimeify/v1/folders?query=" + folderSearch + '&_wpnonce=' + nonce);
          const body = await response.json();
          setFolderResults([defaultFolder].concat(body?.data ? body?.data : []));
        } catch (e) {
          console.warn('Error searching folders:');
          console.warn(e);
          alert('Search error: ' + e.message);
        }
      }
    }, 800);
    return () => clearTimeout(delayDebounceFn);
  }, [folderSearch]);
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    ...blockProps
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: attributes.currentValue ? 'vimeify-upload-form' : ''
  }, attributes.currentValue && '' !== attributes.currentValue && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("iframe", {
    width: "auto",
    height: "400",
    src: 'https://player.vimeo.com/video/' + attributes.currentValue.replace('/videos/', ''),
    frameBorder: "0",
    allow: "autoplay; encrypted-media",
    webkitallowfullscreen: true,
    mozallowfullscreen: true,
    allowFullScreen: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("hr", null), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      textAlign: 'center'
    }
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Button, {
    onClick: handleClear,
    variant: "secondary"
  }, i18n.words.clear))), !attributes.currentValue || '' === attributes.currentValue ? (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h3", {
    className: "vimeify-block-title"
  }, "Vimeo"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginBottom: '15px'
    }
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.RadioControl, {
    label: i18n.words.radio_title,
    selected: type,
    options: [{
      label: methods.upload,
      value: 'upload'
    }, {
      label: methods.local,
      value: 'local'
    }, {
      label: methods.search,
      value: 'search'
    }],
    onChange: value => setType(value)
  })), type === 'upload' && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "vimeify-upload-form-inner"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.TextControl, {
    label: i18n.words.title,
    value: title,
    onChange: value => setTitle(value)
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.TextareaControl, {
    label: i18n.words.description,
    value: description,
    onChange: value => setDescription(value)
  }), isViewPrivacyEnabled && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: i18n.words.view_privacy,
    help: i18n.phrases.view_privacy_help,
    value: viewPrivacy,
    options: Object.keys(viewPrivacyOptions).map(key => {
      return {
        label: viewPrivacyOptions[key].name,
        value: key
      };
    }),
    onChange: newValue => setViewPrivacy(newValue)
  }), isFoldersEnabled && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.TextControl, {
    label: i18n.words.folder,
    placeholder: i18n.phrases.folder_placeholder,
    value: folderSearch,
    help: folderResults.length === 0 ? i18n.phrases.folder_help : "",
    onChange: value => setFolderSearch(value)
  }), folderResults.length > 0 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    help: i18n.phrases.folder_help,
    value: folder,
    options: folderResults.map(item => {
      return {
        label: item.name,
        value: item.uri
      };
    }),
    onChange: newValue => setFolder(newValue)
  })), file && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, "Selected: ", file.name), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.FormFileUpload, {
    accept: "video/*",
    variant: "secondary",
    onChange: event => setFile(event.currentTarget.files[0])
  }, file ? i18n.words.video_replace : i18n.words.video_select), isUploading && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "vimeify-progress"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "vimeify-progress-value",
    style: {
      width: uploadProgress + '%'
    }
  })), file && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginTop: '10px'
    }
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Button, {
    onClick: handleUploadSave,
    variant: "primary"
  }, i18n.words.upload))), type === 'search' && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "vimeify-remote-search-form"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.TextControl, {
    label: i18n.phrases.remote_search_placeholder,
    value: remoteSearch,
    onChange: value => setRemoteSearch(value)
  }), remoteResults.length > 0 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: i18n.words.video_list,
    value: value,
    options: [dropdownPlaceholder].concat(remoteResults.map(item => {
      return {
        label: item.name,
        value: item.uri
      };
    })),
    onChange: selected => setValue(selected)
  }), value && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Button, {
    onClick: saveRemoteSearch,
    variant: "primary"
  }, i18n.words.save)), type === 'local' && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "vimeify-local-search-form"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.TextControl, {
    label: i18n.phrases.local_search_placeholder,
    value: localSearch,
    onChange: value => setLocalSearch(value)
  }), localResults.length > 0 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: i18n.words.video_list,
    value: value,
    options: [dropdownPlaceholder].concat(localResults.map(item => {
      return {
        label: item.name,
        value: item.uri
      };
    })),
    onChange: selected => setValue(selected)
  }), value && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Button, {
    onClick: saveLocalSearch,
    variant: "primary"
  }, i18n.words.save))) : ""));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Edit);

/***/ }),

/***/ "./src/upload/save.js":
/*!****************************!*\
  !*** ./src/upload/save.js ***!
  \****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2023 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
 *
 * This file is property of IDEOLOGIX MEDIA Dooel (https://ideologix.com)
 * This file is part of Vimeify Plugin - https://wordpress.org/plugins/wp-vimeo-videos/
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

const Save = () => {
  return null;
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Save);

/***/ }),

/***/ "./src/upload/editor.scss":
/*!********************************!*\
  !*** ./src/upload/editor.scss ***!
  \********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ ((module) => {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ ((module) => {

module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/data":
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["data"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "./src/upload/block.json":
/*!*******************************!*\
  !*** ./src/upload/block.json ***!
  \*******************************/
/***/ ((module) => {

module.exports = JSON.parse('{"$schema":"https://json.schemastore.org/block.json","apiVersion":2,"name":"vimeify/upload","title":"Vimeify Upload","textdomain":"wp-vimeo-videos","icon":"video-alt","category":"media","example":{},"editorScript":"file:./index.js","editorStyle":["file:./editor.css","vimeify-upload-editor"]}');

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!*****************************!*\
  !*** ./src/upload/index.js ***!
  \*****************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./block.json */ "./src/upload/block.json");
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./edit */ "./src/upload/edit.js");
/* harmony import */ var _save__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./save */ "./src/upload/save.js");
/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2023 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
 *
 * This file is property of IDEOLOGIX MEDIA Dooel (https://ideologix.com)
 * This file is part of Vimeify Plugin - https://wordpress.org/plugins/wp-vimeo-videos/
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






// Destructure the json file to get the name of the block
// For more information on how this works, see: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Operators/Destructuring_assignment
const {
  name
} = _block_json__WEBPACK_IMPORTED_MODULE_1__;

// Register the block
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)(name, {
  attributes: {
    currentValue: {
      type: 'string'
    }
  },
  edit: _edit__WEBPACK_IMPORTED_MODULE_2__["default"],
  save: _save__WEBPACK_IMPORTED_MODULE_3__["default"]
});
})();

/******/ })()
;
//# sourceMappingURL=index.js.map