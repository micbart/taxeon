export function initHeroVideo() {
	const video = document.querySelector('[data-hero-video]');

	if (!video) {
		return;
	}

	const source = video.dataset.src;

	if (!source) {
		return;
	}

	const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	const savesData = navigator.connection?.saveData === true;

	if (prefersReduced || savesData) {
		return;
	}

	const play = () => {
		const promise = video.play();

		if (promise?.catch) {
			promise.catch(() => {});
		}
	};

	video.addEventListener('loadeddata', () => {
		video.dataset.ready = '';
		play();
	});

	if (source.includes('.m3u8')) {
		attachHls(video, source, play);
		return;
	}

	video.src = source;
	video.load();
	play();
}

async function attachHls(video, source, play) {
	try {
		const { default: Hls } = await import('hls.js');

		if (Hls.isSupported()) {
			const hls = new Hls({ capLevelToPlayerSize: true });

			hls.loadSource(source);
			hls.attachMedia(video);
			hls.on(Hls.Events.MANIFEST_PARSED, play);

			return;
		}
	} catch {
	}

	if (video.canPlayType('application/vnd.apple.mpegurl')) {
		video.src = source;
		video.load();
		play();
	}
}
