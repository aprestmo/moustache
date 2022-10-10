/* global $ */
/* eslint-env browser */

function search() {
  const button = $('.js-search-toggle');
  const overlay = document.querySelector('#js-search-overlay');
  const input = document.querySelector('#js-search-field');

  button.on('click', () => {
    overlay.classList.toggle('is-visible');
    input.focus();
  });
}

search();
