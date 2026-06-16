(function () {
	'use strict';

	function bindDeleteConfirmation() {
		document.querySelectorAll('.iadal-departments-wrap .iadal-delete-form').forEach(function (form) {
			form.addEventListener('submit', function (event) {
				if (!window.confirm('Deseja realmente excluir este departamento? Ele sera removido da listagem.')) {
					event.preventDefault();
				}
			});
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		bindDeleteConfirmation();
	});
})();
