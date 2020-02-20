const button = document.getElementById('styleguideRefreshIframe_button')
const iframe = document.getElementById('componentIframe')

button.onclick = function() {refreschFrame()}

function refreschFrame() {
    iframe.contentWindow.location.reload(true);
}
