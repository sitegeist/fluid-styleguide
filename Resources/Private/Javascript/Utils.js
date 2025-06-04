const find = (selector, scope = document) => scope.querySelector(selector)
const findAll = (selector, scope = document) => [].slice.call(scope.querySelectorAll(selector))

const register = (name, component) => {
    document.addEventListener('DOMContentLoaded', () => {
        findAll(`[data-component=${name}]`).forEach(el => component(el))
    })
}

const debounce = (func, timeout = 300) => {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => { func.apply(this, args); }, timeout);
    };
}

export {
    find,
    findAll,
    register,
    debounce,
}
