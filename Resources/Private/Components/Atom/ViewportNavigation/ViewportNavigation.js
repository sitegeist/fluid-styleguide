import {register, findAll, find} from '../../../Javascript/Utils';

const ViewportNavigation = el => {

    const iframes = findAll('.fluidStyleguideComponent');
    const viewPortNavItem = findAll('.viewportItem' , el);
    const rulerOption = find('.rulerOption' , el);
    const dropdownLabel = find('.dropdownLabel', el);
    const listView = el.dataset.listview;

    viewPortNavItem.forEach((item) => {
        const viewPortValue = item.dataset.viewport;
        item.addEventListener('click', ()=>{

            find('.active',el).classList.remove('active');
            item.classList.add('active');

            iframes.forEach((iframe) => {
                iframe.style.width = viewPortValue;
                if(viewPortValue !== '100%' && listView) {
                    iframe.parentElement.classList.add('breakPoint');
                }
            })

            dropdownLabel.textContent = item.textContent;
            viewPortNavItem.forEach((inner) => {
                inner.style.display = 'block';
            });
            item.style.display = 'none';

            document.body.classList.remove('breakPoint');

            if(viewPortValue !== '100%' && !listView) {
                document.body.classList.add('breakPoint');
                /*
                const ruler = iframe.contentWindow.document.getElementById('fluidStyleguideRuler');
                rulerOption.classList.add('activeRulerOption');
                find('span', rulerOption).textContent = rulerOption.dataset.labelon
                ruler.classList.add('show')
                */
            }
        });
    });

    /*
    rulerOption.addEventListener('click', (e)=>{

        const ruler = iframe.contentWindow.document.getElementById('fluidStyleguideRuler');
        rulerOption.classList.add('pressed');
        if(ruler.classList.contains('show')) {
            find('span',e.currentTarget).textContent = e.currentTarget.dataset.labeloff
            ruler.classList.remove('show')
            e.currentTarget.classList.remove('activeRulerOption');
        } else {
            find('span',e.currentTarget).textContent = e.currentTarget.dataset.labelon
            ruler.classList.add('show')
            e.currentTarget.classList.add('activeRulerOption');
        }
    });
    */

};

register('ViewportNavigation', ViewportNavigation);
