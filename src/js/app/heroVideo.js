const MARGINES = '150px';

export function initHeroVideo() {
	const video = document.querySelector('[data-hero-video]');

	if (!video || !video.dataset.src) {
		return;
	}

	const polaczenie = navigator.connection || {};
	const oszczedza = polaczenie.saveData === true || /(^|-)2g$/.test(polaczenie.effectiveType || '');
	const mniejRuchu = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	if (oszczedza || mniejRuchu) {
		return;
	}

	const odtworz = () => {
		if (!video.getAttribute('src')) {
			video.setAttribute('src', video.dataset.src);
			video.load();
		}

		const obietnica = video.play();

		if (obietnica?.catch) {
			obietnica.catch(() => {});
		}
	};

	const obserwator = new IntersectionObserver(
		(wpisy) => {
			wpisy.forEach((wpis) => (wpis.isIntersecting ? odtworz() : video.pause()));
		},
		{ rootMargin: MARGINES }
	);

	obserwator.observe(video);
}
