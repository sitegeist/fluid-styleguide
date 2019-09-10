import { register, find } from '../../../Javascript/Utils';

const StyleguideScrollTop = el => {

    window.addEventListener('scroll', () => {
        if(window.scrollY > 200) {
            el.classList.add('active');
        } else {
            el.classList.remove('active');
        }
    });

    find('div',el).addEventListener('click', () => {
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });

    });
}

register('StyleguideScrollTop', StyleguideScrollTop)

