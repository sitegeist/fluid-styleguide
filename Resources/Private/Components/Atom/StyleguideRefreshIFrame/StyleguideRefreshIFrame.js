const button = document.getElementById('styleguideRefreshIframe_button')
const iframe = document.getElementById('componentIframe')

if (button != null) {
    button.addEventListener('click', function () {
        iframe.contentWindow.location.reload(true)
    })
}

