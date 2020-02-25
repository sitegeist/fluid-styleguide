const button = document.getElementById('styleguideRefreshIframe_button')
const iframe = document.getElementById('componentIframe')

button.addEventListener('click', function(){
    iframe.contentWindow.location.reload(true)
})

