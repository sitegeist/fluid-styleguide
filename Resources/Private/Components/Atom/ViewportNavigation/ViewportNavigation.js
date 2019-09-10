import {register, findAll, find} from '../../../Javascript/Utils';

const ViewportNavigation = el => {

    const iframe = document.getElementById('componentIframe');
    const viewPortNavItem = findAll('.viewportItem' , el);
    const rulerOption = find('.rulerOption' , el);
    const dropdownLabel = find('.dropdownLabel', el);

    viewPortNavItem.forEach((item) => {
        const viewPortValue = item.dataset.viewport;
        item.addEventListener('click', ()=>{

            find('.active',el).classList.remove('active');
            item.classList.add('active');
            iframe.style.width = viewPortValue;
            dropdownLabel.textContent = item.textContent;
            viewPortNavItem.forEach((inner) => {
                inner.style.display = 'block';
            });
            item.style.display = 'none';

            document.body.classList.remove('breakPoint');

            if(viewPortValue !== '100%') {
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
