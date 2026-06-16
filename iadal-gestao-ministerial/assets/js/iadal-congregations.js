(function () {
	'use strict';

	function bindStatusConfirmation() {
		document.querySelectorAll('.iadal-status-form').forEach(function (form) {
			form.addEventListener('submit', function (event) {
				var statusInput = form.querySelector('input[name="status"]');
				var nextStatus = statusInput ? statusInput.value : '';
				var message = 'bloqueado' === nextStatus
					? 'Deseja bloquear esta congregacao? Pastor Local, Secretaria Local e usuarios vinculados ficarao indisponiveis.'
					: 'Deseja desbloquear esta congregacao? Os acessos principais serao liberados.';

				if (!window.confirm(message)) {
					event.preventDefault();
				}
			});
		});
	}

	function bindDeleteConfirmation() {
		document.querySelectorAll('.iadal-congregations-wrap .iadal-delete-form').forEach(function (form) {
			form.addEventListener('submit', function (event) {
				if (!window.confirm('Deseja realmente excluir esta congregacao? Ela sera removida da listagem e seus usuarios serao inativados.')) {
					event.preventDefault();
				}
			});
		});
	}

	function bindPasswordResetConfirmation() {
		document.querySelectorAll('.iadal-reset-password-form').forEach(function (form) {
			form.addEventListener('submit', function (event) {
				if (!window.confirm('Gerar uma nova senha provisoria para este usuario? A senha atual deixara de funcionar.')) {
					event.preventDefault();
				}
			});
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		bindStatusConfirmation();
		bindDeleteConfirmation();
		bindPasswordResetConfirmation();
	});
})();
