/**
 * Collapsible sub-navigation
 *
 * Injects an accessible toggle button next to each parent nav link and
 * handles expand / collapse with aria-expanded state.
 * ES modules are deferred by default, so the DOM is ready when this runs.
 */

const parentItems = document.querySelectorAll('.c-nav__item--parent')
let counter = 0

parentItems.forEach((item) => {
  const link = item.querySelector(':scope > .c-nav__link')
  const subMenu = item.querySelector(':scope > .c-nav__sub-menu')
  if (!link || !subMenu) return

  // Unique ID for aria-controls
  const id = `c-nav-sub-${++counter}`
  subMenu.id = id

  // Build chevron toggle button
  const btn = document.createElement('button')
  btn.className = 'c-nav__toggle'
  btn.type = 'button'
  btn.setAttribute('aria-controls', id)
  btn.setAttribute('aria-label', `Expand ${link.textContent.trim()}`)
  btn.innerHTML =
    '<svg class="c-icon" width="16" height="16" viewBox="0 0 16 16" fill="none" ' +
    'stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" ' +
    'aria-hidden="true" focusable="false">' +
    '<polyline points="4,6 8,10 12,6"/>' +
    '</svg>'

  // Auto-expand the currently active section on load
  const isActive =
    item.classList.contains('c-nav__item--parent--active') ||
    item.classList.contains('c-nav__item--ancestor--active')

  btn.setAttribute('aria-expanded', String(isActive))
  if (isActive) item.classList.add('is-open')

  // Insert button immediately after the parent link
  link.insertAdjacentElement('afterend', btn)

  // Toggle on click
  btn.addEventListener('click', () => {
    const expanded = btn.getAttribute('aria-expanded') === 'true'
    btn.setAttribute('aria-expanded', String(!expanded))
    item.classList.toggle('is-open')
  })
})
