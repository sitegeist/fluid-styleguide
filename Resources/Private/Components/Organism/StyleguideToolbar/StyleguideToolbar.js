import {register, findAll, find} from '../../../Javascript/Utils';

const StyleguideToolbar = el => {

    const toolbarOpener = find('.toolbarOpener', el);
    const tabOpeners = findAll('.tabOpener', el);
    const tabContents = findAll('.tabContent', el);


    el.style.bottom = `-${el.clientHeight-16}px`;

    toolbarOpener.addEventListener('click', ()=>{

        if(!el.classList.toggle('open')) {
            tabContents.forEach((content, contentIndex) => {
                if (!contentIndex) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });
            tabOpeners.forEach((opener, openerIndex) => {
                if (!openerIndex) {
                    opener.classList.add('active');
                } else {
                    opener.classList.remove('active');
                }
            });
        }
    })

    tabOpeners.forEach((opener, openerIndex) => {
        opener.addEventListener('click', ()=>{
            find('.tabOpener.active').classList.remove('active');
            find('.tabContent.active').classList.remove('active');
            tabContents.forEach((content, contentIndex) => {
                if (openerIndex === contentIndex) {
                    opener.classList.add('active');
                    content.classList.add('active');
                }
            });
        })
    });

    const docTab = document.querySelector('.documentation')
    const docTabInner = document.querySelector('.documentation .boxMargin')
    const docHeight = parseFloat(window.getComputedStyle(docTabInner, null).getPropertyValue("height")) + 30
    if (docHeight > 500 && docHeight < 600) {
      docTab.classList.add('maxHeight500')
    } else if (docHeight > 600 && docHeight < 700) {
      docTab.classList.add('maxHeight600')
    } else if (docHeight > 700 && docHeight < 800) {
      docTab.classList.add('maxHeight700');
    }
};

register('StyleguideToolbar', StyleguideToolbar);
