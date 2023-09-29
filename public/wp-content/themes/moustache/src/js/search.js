function search() {
	const button = document.querySelector('[data-toggle="search"]')
	const overlay = document.querySelector('#js-search-overlay')
	const input = document.querySelector('#js-search-field')

	button.addEventListener('click', () => {
		overlay.classList.toggle('is-visible')
		setTimeout(() => {
			input.focus()
		}, 100)
	})

	document.addEventListener('keyup', (e) => {
		if (overlay.classList.contains('is-visible')) {
			if (e.key === 'Escape') {
				overlay.classList.toggle('is-visible')
			}
		}
	})
}

search()
