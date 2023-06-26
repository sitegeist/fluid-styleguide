import { findAll } from '../../../Javascript/Utils';
const button = document.getElementById('styleguideRefreshIframe_button')

if (button != null) {
    button.addEventListener('click', function () {
        findAll(`iframe[name="componentIframe"]`).forEach(iframe => {
            iframe.contentWindow.location.reload(true)
        })
    })
}

