/**
 * Sortable tables
 *
 * Adds click-to-sort to every column of any table marked with .js-sortable.
 * Clicking a header cycles ascending → descending → original order. A column
 * sorts numerically when all of its cells parse as numbers, otherwise
 * locale-aware as strings. ES modules are deferred by default, so the DOM is
 * ready when this runs.
 */

document.querySelectorAll('table.js-sortable').forEach((table) => {
  const tbody = table.querySelector('tbody')
  const headers = Array.from(table.querySelectorAll('thead th'))
  if (!tbody || headers.length === 0) return

  const rows = Array.from(tbody.querySelectorAll('tr'))
  const originalOrder = rows.slice()

  const cellValue = (row, index) => row.children[index]?.textContent.trim() ?? ''

  const isNumericColumn = (index) =>
    rows.every((row) => {
      const value = cellValue(row, index)
      return value === '' || !Number.isNaN(Number(value))
    })

  headers.forEach((th, index) => {
    const label = th.textContent.trim()
    const button = document.createElement('button')
    button.type = 'button'
    button.className = 'table-sort__button'
    button.setAttribute('aria-label', `Sort by ${label}`)
    while (th.firstChild) button.appendChild(th.firstChild)

    let direction = 0 // 0 = original, 1 = ascending, 2 = descending

    button.addEventListener('click', () => {
      direction = (direction + 1) % 3

      headers.forEach((header) => {
        header.removeAttribute('aria-sort')
        header.classList.remove('is-asc', 'is-desc')
      })

      if (direction === 0) {
        originalOrder.forEach((row) => tbody.appendChild(row))
        return
      }

      const numeric = isNumericColumn(index)
      rows
        .slice()
        .sort((a, b) => {
          const comparison = numeric
            ? Number(cellValue(a, index)) - Number(cellValue(b, index))
            : cellValue(a, index).localeCompare(cellValue(b, index), 'nb')
          return direction === 1 ? comparison : -comparison
        })
        .forEach((row) => tbody.appendChild(row))

      th.setAttribute('aria-sort', direction === 1 ? 'ascending' : 'descending')
      th.classList.add(direction === 1 ? 'is-asc' : 'is-desc')
    })

    th.appendChild(button)
  })
})
