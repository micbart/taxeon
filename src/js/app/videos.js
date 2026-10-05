export function initVideos(root = document) {
	root.querySelectorAll('[data-video-play]').forEach((button) => {
		button.addEventListener('click', () => {
			const ramka = document.createElement('iframe');

			ramka.src = button.dataset.embed;
			ramka.title = button.dataset.title ?? '';
			ramka.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
			ramka.allowFullscreen = true;
			ramka.className = 'absolute inset-0 size-full border-0';

			button.replaceWith(ramka);
		});
	});
}
