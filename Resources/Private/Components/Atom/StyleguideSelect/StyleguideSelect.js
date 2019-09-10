import {register, find, findAll} from '../../../Javascript/Utils';
import {StyleguideSelectHelper} from './StyleguideSelectHelper';

const StyleguideSelect = el => {

    if (el) {

        // options
        const autoSuggest = el.dataset.autosuggest ? el.dataset.autosuggest : false
        const submitFormOnSelect = el.dataset.submitformonselect ? el.dataset.submitformonselect : false

        const classPrefix = 'styleguideSelect';
        const nativeSelectElement = el.getElementsByTagName('select')[0];
        const selectLabel = StyleguideSelectHelper.buildFormCustomSelectLabel(nativeSelectElement, autoSuggest);
        const select = StyleguideSelectHelper.buildFormCustomSelect(nativeSelectElement, submitFormOnSelect);
        const fakeOptions = findAll('div', select);
        const autoSuggestInput = find('input', selectLabel);

        // append custom select and custom select label
        el.appendChild(selectLabel);
        el.appendChild(select);


        let preSelected;
        // handle auto suggest if set in options
        if (autoSuggest && autoSuggestInput) {

            autoSuggestInput.addEventListener('input', (e) => {
                StyleguideSelectHelper.handleAutoSuggestInput(fakeOptions, select, e.currentTarget);
            })

            autoSuggestInput.addEventListener('focus', (e) => {
                e.currentTarget.value = '';
            })

            autoSuggestInput.addEventListener('blur', (e) => {
                StyleguideSelectHelper.handleAutoSuggestBlur(fakeOptions, select, e.currentTarget)
            })

            preSelected = findAll('div', select).find(h => (h.innerHTML === find('input', selectLabel).value.trim()));

        } else {
            // preselect option
            preSelected = findAll('div', select).find(h => (h.innerHTML === selectLabel.textContent))
        }

        if (typeof preSelected !== 'undefined') {
            preSelected.classList.add(`${classPrefix}EqualSelected`);
        }

        // apply click event to custom select label
        selectLabel.addEventListener('click', (e) => {
            e.stopPropagation();
            e.preventDefault();

            if (autoSuggest && autoSuggestInput) {
                autoSuggestInput.focus();
            }

            StyleguideSelectHelper.toggleSelect(select);

        });

        // apply keyboard events to custom select
        document.addEventListener('keydown', event => {
            StyleguideSelectHelper.handleSelectNavigationKeypressDown(select, event);
            StyleguideSelectHelper.handleSelectNavigationKeypressUp(select, event);
            StyleguideSelectHelper.handleSelectNavigationKeypressEnter(select, event);
            StyleguideSelectHelper.handleSelectNavigationKeypressEscape(select, event);
        });

        // simulate select focus

        nativeSelectElement.addEventListener('focus', event => {
            StyleguideSelectHelper.simulateSelectFocus(select, event);
        })

        nativeSelectElement.addEventListener('blur', event => {
            StyleguideSelectHelper.simulateSelectBlur(select, event);
        })

    }
};

register('StyleguideSelect', StyleguideSelect);
