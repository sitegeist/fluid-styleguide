import {find, findAll} from '../../../Javascript/Utils'
import '../../../Javascript/Polyfills'

/**
 * Class StyleguideSelectHelper Custom Select Helper
 */

class StyleguideSelectHelper {

    static classPrefix = 'styleguideSelect'

    /**
     * Builds a custom select
     *
     * @param {HTMLSelectElement} nativeSelectElement The Select Element
     * @param {boolean} submitFormOnSelect determines if the form should be submitted on selection
     * @return {HTMLElement} returns the custom select element
     */

    static buildFormCustomSelect(nativeSelectElement, submitFormOnSelect = false) {

        const options = findAll('option', nativeSelectElement)

        if (!options) {
            return
        }

        //get parent form if submitFormOnSelect is set
        const parentForm = submitFormOnSelect ? nativeSelectElement.closest('form') : false

        // create custom select
        const select = document.createElement('DIV')
        select.setAttribute('class', `${StyleguideSelectHelper.classPrefix}Items`)

        for (let j = 0; j < options.length; j++) {

            const fakeOption = document.createElement('DIV')

            fakeOption.innerHTML = nativeSelectElement.options[j].innerHTML.trim()

            if (options[j].dataset.url) {
                fakeOption.setAttribute('data-url', options[j].dataset.url)
            }

            fakeOption.addEventListener('mousedown', (e) => {
                e.preventDefault();
            })

            fakeOption.addEventListener('click', (e) => {

                e.preventDefault();
                e.stopPropagation();

                if (e.currentTarget.dataset.url) {
                    location.href = e.currentTarget.dataset.url;
                    return false;
                }

                const previousSibling = e.currentTarget.parentNode.previousSibling
                const formCustomSelectEqualSelected = e.currentTarget.parentNode.getElementsByClassName(`${StyleguideSelectHelper.classPrefix}EqualSelected`)

                if (nativeSelectElement.options[j].dataset.reset) {
                    previousSibling.innerHTML = nativeSelectElement.options[j].innerHTML
                    nativeSelectElement.selectedIndex = 0
                    for (let k = 0; k < formCustomSelectEqualSelected.length; k++) {
                        formCustomSelectEqualSelected[k].removeAttribute('class')
                    }

                } else {
                    if (nativeSelectElement.options[j].innerHTML === e.currentTarget.innerHTML) {
                        nativeSelectElement.selectedIndex = j
                        previousSibling.innerHTML = e.currentTarget.innerHTML

                        for (let k = 0; k < formCustomSelectEqualSelected.length; k++) {
                            formCustomSelectEqualSelected[k].removeAttribute('class')
                        }

                        e.currentTarget.setAttribute('class', `${StyleguideSelectHelper.classPrefix}EqualSelected`)
                    }
                    // submit form on select if parentForm has been passed ( no reset possible )
                    if (submitFormOnSelect && parentForm) {
                        parentForm.submit()
                    }
                }
                nativeSelectElement.focus();
                StyleguideSelectHelper.toggleSelect(select)
            })
            if (!nativeSelectElement.options[j].dataset.reset) {
                select.appendChild(fakeOption)
            }

        }
        return select
    }

    /**
     * Builds a custom select label
     *
     * @param {HTMLSelectElement} nativeSelectElement The Select Element
     * @param {boolean} autoSuggest has auto suggest option to append a input field
     * @return {HTMLElement} returns the custom select label
     */

    static buildFormCustomSelectLabel(nativeSelectElement, autoSuggest) {
        const fakeSelectedLabel = document.createElement('DIV')
        const value = nativeSelectElement.options[nativeSelectElement.selectedIndex].innerHTML.trim();
        fakeSelectedLabel.classList.add(`${StyleguideSelectHelper.classPrefix}Selected`);

        if (autoSuggest) {
            const autoSuggestInput = document.createElement('input')
            autoSuggestInput.classList.add(`${StyleguideSelectHelper.classPrefix}AutosuggestInput`)

            autoSuggestInput.setAttribute('type', 'text')
            autoSuggestInput.setAttribute('data-value', value)
            autoSuggestInput.setAttribute('placeholder', value)
            autoSuggestInput.value = value
            fakeSelectedLabel.appendChild(autoSuggestInput)


            const inputLabel = document.createElement('DIV')
            inputLabel.textContent = value;

            fakeSelectedLabel.appendChild(inputLabel)
            inputLabel.classList.add(`${StyleguideSelectHelper.classPrefix}InputLabel`);

            setTimeout(function() {
                fakeSelectedLabel.style.width = `${inputLabel.clientWidth}px`;
            }, 20);


        } else {
            fakeSelectedLabel.innerHTML = value
        }

        return fakeSelectedLabel
    }

    /**
     * Handles Custom Select Focus
     *
     * @param {HTMLElement} select The custom Select Element
     * @param {Event} event KeyboardEvent
     */

    static simulateSelectFocus(select, event) {

        if (event.relatedTarget !== null) {
            StyleguideSelectHelper.toggleSelect(select);
        }

    }

    /**
     * Handles Custom Select Blur
     *
     * @param {HTMLElement} select The custom Select Element
     * @param {Event} event KeyboardEvent
     */

    static simulateSelectBlur(select, event) {
        if (select.classList.contains(`${StyleguideSelectHelper.classPrefix}Opened`)) {
            StyleguideSelectHelper.toggleSelect(select);
        }
    }

    /**
     * Handles Custom Select Navigation on Keypress Down
     *
     * @param {HTMLElement} select The custom Select Element
     * @param {Event} event KeyboardEvent
     */

    static handleSelectNavigationKeypressDown(select, event) {

        if (event.isComposing || event.keyCode !== 40 || !select.classList.contains(`${StyleguideSelectHelper.classPrefix}Opened`)) {
            return
        }

        event.preventDefault()

        const firstOption = find(`.${StyleguideSelectHelper.classPrefix}Items div`, select)

        if (!find(`.${StyleguideSelectHelper.classPrefix}Active`, select)) {
            firstOption.classList.add(`${StyleguideSelectHelper.classPrefix}Active`)
        } else {
            const selectedOption = find(`.${StyleguideSelectHelper.classPrefix}Active`, select)
            if (!selectedOption.nextSibling) {
                return
            }
            selectedOption.nextSibling.classList.add(`${StyleguideSelectHelper.classPrefix}Active`)
            selectedOption.classList.remove(`${StyleguideSelectHelper.classPrefix}Active`)
            select.scrollTop = selectedOption.nextSibling.offsetTop
        }

    }

    /**
     * Handles Custom Select Navigation on Keypress Up
     *
     * @param {HTMLElement} select The custom Select Element
     * @param {Event} event KeyboardEvent
     */

    static handleSelectNavigationKeypressUp(select, event) {

        if (event.isComposing || event.keyCode !== 38 || !(select.classList.contains(`${StyleguideSelectHelper.classPrefix}Opened`)
            && find(`.${StyleguideSelectHelper.classPrefix}Active`, select))) {
            return
        }

        event.preventDefault()

        const firstOption = find('.selectItems div', select)
        const selectedOption = find(`.${StyleguideSelectHelper.classPrefix}Active`, select)
        if (selectedOption === firstOption || !selectedOption.previousSibling) {
            return
        }
        selectedOption.previousSibling.classList.add(`${StyleguideSelectHelper.classPrefix}Active`)
        selectedOption.classList.remove(`${StyleguideSelectHelper.classPrefix}Active`)
        select.scrollTop = selectedOption.previousSibling.offsetTop

    }

    /**
     * Handles Custom Select Navigation on Keypress Enter
     *
     * @param {HTMLElement} select The custom Select Element
     * @param {Event} event KeyboardEvent
     */

    static handleSelectNavigationKeypressEnter(select, event) {

        if (event.isComposing || event.keyCode !== 13) {
            return
        }

        if (select.classList.contains(`${StyleguideSelectHelper.classPrefix}Opened`)
            && find(`.${StyleguideSelectHelper.classPrefix}Active`, select)) {
            event.preventDefault()
            const selectedOption = find(`.${StyleguideSelectHelper.classPrefix}Active`, select)
            const clickEvent = new Event('click')
            selectedOption.dispatchEvent(clickEvent)
        }
    }

    /**
     * Handles Custom Select Navigation on Keypress Escape
     *
     * @param {HTMLElement} select The custom Select Element
     * @param {Event} event KeyboardEvent
     */

    static handleSelectNavigationKeypressEscape(select, event) {

        if (event.isComposing || event.keyCode !== 27) {
            return
        }

        StyleguideSelectHelper.toggleSelect(select)
    }

    /**
     * Handles Auto Suggest Input Input event
     *
     * @param {NodeList} fakeOptions
     * @param {HTMLElement} select
     * @param {HTMLInputElement} autoSuggestInput
     */
    static handleAutoSuggestInput(fakeOptions, select, autoSuggestInput) {

        const fakeOptionsOnSuggest = findAll('div', select);
        const value = autoSuggestInput.value;

        if (!value) {
            fakeOptions.forEach((element) => {
                select.appendChild(element);
            });

            StyleguideSelectHelper.setSelectFlyOutHeight(select);
            return;
        }

        const keywords = value.split(' ');
        const autoSuggestResult = fakeOptions.filter(h => keywords.every(k => h.textContent.toLowerCase().includes(k.toLowerCase())));

        fakeOptionsOnSuggest.forEach((element) => {
            select.removeChild(element);
        });
        autoSuggestResult.forEach((element) => {
            select.appendChild(element);
        });
        StyleguideSelectHelper.setSelectFlyOutHeight(select);
    }

    /**
     * Handles Auto Suggest Input Blur event
     *
     * @param {NodeList} fakeOptions
     * @param {HTMLElement} select
     * @param {HTMLInputElement} autoSuggestInput
     */
    static handleAutoSuggestBlur (fakeOptions, select, autoSuggestInput) {

        const fakeOptionsOnSuggest = findAll('div', select);
        fakeOptionsOnSuggest.forEach((element) => {
            select.removeChild(element);
        });
        fakeOptions.forEach((element) => {
            select.appendChild(element);
        });
        autoSuggestInput.value = autoSuggestInput.dataset.value;
        StyleguideSelectHelper.setSelectFlyOutHeight(select);
    }

    /**
     * Handles custom select toggle
     *
     * @param {HTMLElement} select The custom Select Element
     */

    static toggleSelect(select) {
        const allSelects = document.querySelectorAll(`.${StyleguideSelectHelper.classPrefix}Opened`)
        const keyboardSelectedSelect = find(`.${StyleguideSelectHelper.classPrefix}Active`, select)
        const autoSuggest = find(`.${StyleguideSelectHelper.classPrefix}AutosuggestInput`, select.previousElementSibling)

        if (select.classList.toggle(`${StyleguideSelectHelper.classPrefix}Opened`)) {
            StyleguideSelectHelper.setSelectFlyOutHeight(select);
            for (let element = 0; element < allSelects.length; element++) {

                if (allSelects[element] !== select) {
                    allSelects[element].classList.remove(`${StyleguideSelectHelper.classPrefix}Opened`)
                    allSelects[element].previousSibling.classList.remove(`${StyleguideSelectHelper.classPrefix}ArrowActive`)
                }
            }
            document.addEventListener('click',  StyleguideSelectHelper.closeSelect);
        } else {
            if(autoSuggest) {
                autoSuggest.blur()
            }
        }

        select.previousSibling.classList.toggle(`${StyleguideSelectHelper.classPrefix}ArrowActive`)

        if (keyboardSelectedSelect) {
            keyboardSelectedSelect.classList.remove(`${StyleguideSelectHelper.classPrefix}Active`)
        }

    }

    /**
     * closes the select
     */

    static closeSelect () {
        const openSelect = document.querySelector(`.${StyleguideSelectHelper.classPrefix}Opened`);
        document.removeEventListener('click',  StyleguideSelectHelper.closeSelect);

        if(openSelect) {
            openSelect.classList.remove(`${StyleguideSelectHelper.classPrefix}Opened`);
            openSelect.previousSibling.classList.remove(`${StyleguideSelectHelper.classPrefix}ArrowActive`)
        }

    }


    /**
     * Set select container maxHeight and Height to fit into viewport
     *
     * @param {HTMLElement} select The Custom Select Label
     */

    static setSelectFlyOutHeight(select) {

        select.removeAttribute('style')

        const selectLabel = find(`.${StyleguideSelectHelper.classPrefix}Selected`, select.parentNode)
        const selectDropdownMaxHeight = (window.innerHeight - selectLabel.getBoundingClientRect().top) - selectLabel.offsetHeight
        const height = selectLabel.offsetHeight * select.childElementCount
        const selectDropdownMaxHeightFixed = select.offsetHeight > selectDropdownMaxHeight;
        let cssHeightAndMaxHeight = `height:${height + 2}px;max-height:${selectDropdownMaxHeightFixed + 2}px;`;

        if (selectDropdownMaxHeightFixed || select.parentElement.classList.contains('orientationBottom')) {
            // position select at top
            cssHeightAndMaxHeight = `height:auto;top:-${select.offsetHeight}px`;
            select.classList.add('selectPositionedTop');
        } else {
            select.classList.remove('selectPositionedTop');
        }

        select.setAttribute('style', cssHeightAndMaxHeight)

    }
}

export {StyleguideSelectHelper}
