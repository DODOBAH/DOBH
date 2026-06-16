(function () {
	'use strict';

	function bindDeleteConfirmation() {
		document.querySelectorAll('.iadal-departments-wrap .iadal-delete-form').forEach(function (form) {
			form.addEventListener('submit', function (event) {
				if (!window.confirm('Deseja realmente remover este registro? A acao sera registrada no historico.')) {
					event.preventDefault();
				}
			});
		});
	}

	function bindPasswordResetConfirmation() {
		document.querySelectorAll('.iadal-departments-wrap .iadal-reset-password-form').forEach(function (form) {
			form.addEventListener('submit', function (event) {
				if (!window.confirm('Gerar uma nova senha provisoria? A senha atual deixara de funcionar.')) {
					event.preventDefault();
				}
			});
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		bindDeleteConfirmation();
		bindPasswordResetConfirmation();
	});
})();
