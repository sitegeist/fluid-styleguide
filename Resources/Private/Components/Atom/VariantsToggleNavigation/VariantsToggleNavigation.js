import {register, find} from '../../../Javascript/Utils';

const VariantsToggleNavigation = el => {
    if (el) {
        const checkbox = find('input[type="checkbox"]', el);
        checkbox.addEventListener('change', event => {
            let url = new URL(window.location.href);
            url.searchParams.set('variants', event.target.checked ? 1 : 0);
            window.location = url.toString();
        });
    }
}

register('VariantsToggleNavigation', VariantsToggleNavigation);
