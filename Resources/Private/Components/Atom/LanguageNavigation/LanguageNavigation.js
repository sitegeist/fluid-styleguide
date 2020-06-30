import {register, findAll, find} from '../../../Javascript/Utils';

const LanguageNavigation = el => {

    const iframes = findAll('.fluidStyleguideComponent');
    const languageNavItem = findAll('.languageItem' , el);
    const dropdownLabel = find('.dropdownLabel', el);
    // const listView = el.dataset.listview;

    languageNavItem.forEach((item) => {
        const languageValue = item.dataset.language;
        item.addEventListener('click', ()=>{
            find('.active',el).classList.remove('active');
            item.classList.add('active');

            iframes.forEach((iframe) => {
                let url = new URL(iframe.src);
                url.searchParams.set('language', languageValue);
                iframe.src = url.toString();
            })

            dropdownLabel.textContent = item.textContent;
            languageNavItem.forEach((inner) => {
                inner.style.display = 'block';
            });
            item.style.display = 'none';

            document.body.classList.remove('breakPoint');
        });
    });

};

register('LanguageNavigation', LanguageNavigation);
