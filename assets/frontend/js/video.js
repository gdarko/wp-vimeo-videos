function _toConsumableArray(arr) {
    if (Array.isArray(arr)) {
        for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) {
            arr2[i] = arr[i];
        }
        return arr2;
    } else {
        return Array.from(arr);
    }
}

document.addEventListener('DOMContentLoaded', function (e) {
    let listener = function (e) {
        var wrapper = e.target.closest(".dgv-embed-modern");
        if (wrapper) {
            var preview = wrapper.querySelector(".dgv-embed-modern-video-preview-image");
            if (preview) {
                var i = document.createElement("iframe");
                _toConsumableArray(preview.attributes).forEach(function (e) {
                    "class" !== e.name && "style" !== e.name && ("data-iframe-src" !== e.name ? i.setAttribute(e.name, e.value) : i.setAttribute("src", e.value));
                });
                preview.replaceWith(i);
            }
            var overlay = wrapper.querySelector('.dgv-embed-modern-video-overlay');
            overlay.style.display = 'none';
            var icon = wrapper.querySelector('.dgv-embed-modern-video-overlay-icon');
            icon.style.display = 'none';
            var iframe = wrapper.querySelector("iframe");
            iframe && iframe.getAttribute("src") && (iframe.src += "&autoplay=1");
            var video = wrapper.querySelector("video");
            video && video.play();
        }
    }
    let clickables = document.querySelectorAll('.dgv-embed-modern-video-overlay, .dgv-embed-modern-video-preview-image, .dgv-embed-modern-video-overlay-icon');
    for (var i = 0; i < clickables.length; i++) {
        clickables[i].addEventListener("click", listener);
    }
});