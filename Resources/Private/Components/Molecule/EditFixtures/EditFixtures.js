import {register, findAll, find, debounce} from '../../../Javascript/Utils';

const EditFixtures = el => {

    const form = find('form', el);
    const inputs = findAll('.editFixturesInput', el);
    const checkboxes = findAll('.editFixturesCheckbox', el);

    inputs.forEach((input) => {
        input.addEventListener('input', debounce(()=>{
            form.submit();
        }))
    });

    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', debounce(()=>{
            form.submit();
        }))
    });
};

register('EditFixtures', EditFixtures);
