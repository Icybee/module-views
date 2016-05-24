!function (Brickrouge) {

	/**
	 * @param {Element} el
	 *
	 * @returns {boolean}
	 */
	function isActive(el) {
		return el.classList.contains('active')
	}

	/**
	 * @param {Element} el
	 */
	function activate(el) {
		el.classList.add('active')
	}

	/**
	 * @param {Element} el
	 */
	function deactivate(el) {
		el.classList.remove('active')
	}

	/**
	 * @param {NodeList} nodeList
	 * @param {Element} el
	 *
	 * @returns {number}
	 */
	function indexOf(nodeList, el) {
		return Array.prototype.indexOf.call(nodeList, el)
	}

	Brickrouge.observe(Brickrouge.EVENT_UPDATE, ev => {

		ev.fragment.querySelectorAll('.view-editor').forEach(editor => {

			if (editor.retrieve('editor')) return

			editor.store('editor', 'inline')

			const categories = editor.querySelectorAll('td.view-editor-categories li')
			const subcategories = editor.querySelectorAll('td.view-editor-subcategories ul')
			const subcategoriesEntries = editor.querySelectorAll('td.view-editor-subcategories li')
			const views = editor.querySelectorAll('td.view-editor-views ul')

			function setCategory(index) {
				const category = categories[index]

				if (isActive(category)) {
					return
				}

				categories.forEach(deactivate)

				activate(category)

				clearSubCategories()

				setSubCategories(index)
			}

			function clearSubCategories() {
				subcategories.forEach(deactivate)

				clearSubCategoriesEntries()
			}

			function clearSubCategoriesEntries() {
				subcategoriesEntries.forEach(deactivate)

				clearViews()
			}

			function clearViews() {
				views.forEach(deactivate)
			}

			function setSubCategories(index) {
				const target = subcategories[index]

				if (isActive(target)) {
					return
				}

				clearSubCategories()

				activate(target)
			}

			function setSubCategory(index) {
				const target = subcategoriesEntries[index]

				if (isActive(target)) {
					return
				}

				clearSubCategoriesEntries()

				activate(target)

				setViews(index)
			}

			function setViews(index) {
				const target = views[index]

				if (isActive(target)) {
					return
				}

				clearViews()

				activate(target)
			}

			function checkChecked(input) {
				const container = input.closest('li')

				container.parentNode.childNodes.forEach(deactivate)
				activate(container)
			}

			editor.addEventListener('click', ev => {

				let target = ev.target

				if (target.get('tag') == 'a') {
					target = target.closest('li')
				}

				let i = indexOf(categories, target)

				if (i != -1) {
					setCategory(i)
					return
				}

				i = indexOf(subcategoriesEntries, target)

				if (i != -1) {
					setSubCategory(i)
					return
				}

				if (target.matches('input[type="radio"]')) {
					checkChecked(target)
				}
			})

			const selected = editor.querySelector('input[checked]')

			if (selected) {
				const container = selected.closest('li')

				container.classList.add('selected')
				activate(container)
			}
		})
	})

} (Brickrouge)
