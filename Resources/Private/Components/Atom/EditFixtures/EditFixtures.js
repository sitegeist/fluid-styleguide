import {register, findAll, find} from '../../../Javascript/Utils';

const EditFixtures = el => {

    const form = find('form', el);
    const inputs = findAll('.editFixturesInput', el);
    const checkboxes = findAll('.editFixturesCheckbox', el);

    inputs.forEach((input) => {
        input.addEventListener('input', ()=>{
            form.submit();

        })
    });

    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', ()=>{
            form.submit();
        })
    });

};

register('EditFixtures', EditFixtures);
