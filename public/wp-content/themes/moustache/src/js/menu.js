/* eslint-env browser */

const buttonOpen = document.querySelector('#js-menu-toggle--open')
const buttonClose = document.querySelector('#js-menu-toggle--close')
const navigationPanel = document.querySelector('#js-site-navigation')

buttonOpen.addEventListener('click', () => {
  navigationPanel.classList.toggle('is-visible')
})

buttonClose.addEventListener('click', () => {
  navigationPanel.classList.toggle('is-visible')
})
