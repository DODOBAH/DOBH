(function () {
	'use strict';

	function formatCpf(value) {
		var digits = value.replace(/\D+/g, '').slice(0, 11);

		if (digits.length <= 3) {
			return digits;
		}

		if (digits.length <= 6) {
			return digits.slice(0, 3) + '.' + digits.slice(3);
		}

		if (digits.length <= 9) {
			return digits.slice(0, 3) + '.' + digits.slice(3, 6) + '.' + digits.slice(6);
		}

		return digits.slice(0, 3) + '.' + digits.slice(3, 6) + '.' + digits.slice(6, 9) + '-' + digits.slice(9);
	}

	function bindCpfMasks() {
		document.querySelectorAll('[data-iadal-cpf]').forEach(function (input) {
			input.addEventListener('input', function () {
				input.value = formatCpf(input.value);
			});
		});
	}

	function bindPhotoPreview() {
		document.querySelectorAll('[data-iadal-photo-input]').forEach(function (input) {
			input.addEventListener('change', function () {
				var preview = document.querySelector('[data-iadal-photo-preview]');
				var file = input.files && input.files[0] ? input.files[0] : null;

				if (!preview || !file) {
					return;
				}

				if (!file.type.match(/^image\//)) {
					return;
				}

				preview.src = URL.createObjectURL(file);
				preview.classList.add('is-visible');
			});
		});
	}

	function bindDeleteConfirmation() {
		document.querySelectorAll('.iadal-delete-form').forEach(function (form) {
			form.addEventListener('submit', function (event) {
				var confirmed = window.confirm('Deseja realmente excluir este membro? Ele sera removido da listagem.');

				if (!confirmed) {
					event.preventDefault();
				}
			});
		});
	}

	function bindEntryTypeVisibility() {
		document.querySelectorAll('[data-iadal-entry-type]').forEach(function (select) {
			var form = select.closest('form');
			var changeLetter = form ? form.querySelector('[data-iadal-change-letter]') : null;
			var acclamationLetter = form ? form.querySelector('[data-iadal-acclamation-letter]') : null;

			function refresh() {
				if (changeLetter) {
					changeLetter.style.display = 'mudanca' === select.value ? '' : 'none';
				}

				if (acclamationLetter) {
					acclamationLetter.style.display = 'aclamacao' === select.value ? '' : 'none';
				}
			}

			select.addEventListener('change', refresh);
			refresh();
		});
	}

	function bindPrintButtons() {
		document.querySelectorAll('[data-iadal-print]').forEach(function (button) {
			button.addEventListener('click', function () {
				window.print();
			});
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		bindCpfMasks();
		bindPhotoPreview();
		bindDeleteConfirmation();
		bindEntryTypeVisibility();
		bindPrintButtons();
	});
})();
