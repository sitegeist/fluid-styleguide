const find = (selector, scope = document) => scope.querySelector(selector)
const findAll = (selector, scope = document) => [].slice.call(scope.querySelectorAll(selector))

const register = (name, component) => {
  document.addEventListener('DOMContentLoaded', () => {
    findAll(`[data-component=${name}]`).forEach(el => component(el))
  })
}

export {
  find,
  findAll,
  register
}
