import 'iframe-resizer/js/iframeResizer.contentWindow';

// close select if clicked outside
document.addEventListener('click', () => {
    const selectOpened = window.top.document.querySelector('.styleguideSelectOpened');
    if (selectOpened) {
        window.top.document.querySelector('.styleguideSelectOpened').classList.remove('styleguideSelectOpened');
    }
});
