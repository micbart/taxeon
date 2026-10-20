export function initMatrix(root = document) {
	root.querySelectorAll('[data-matrix]').forEach((tabela) => {
		const przyciski = [...tabela.querySelectorAll('[data-matrix-pakiet]')];

		przyciski.forEach((przycisk) => {
			przycisk.addEventListener('click', () => {
				tabela.dataset.wybrany = przycisk.dataset.matrixPakiet;
				przyciski.forEach((inny) => inny.setAttribute('aria-pressed', String(inny === przycisk)));
			});
		});
	});
}
