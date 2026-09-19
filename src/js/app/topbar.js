const ODSTEP = 6000;

export function initTopbar() {
	const feed = document.querySelector('[data-topbar-feed]');

	if (!feed) {
		return;
	}

	const items = feed.querySelectorAll('a');

	if (items.length < 2) {
		return;
	}

	let index = 0;
	let timer = null;

	const show = (next) => {
		items[index].removeAttribute('data-active');
		index = next;
		items[index].setAttribute('data-active', '');
	};

	const start = () => {
		if (timer === null) {
			timer = window.setInterval(() => show((index + 1) % items.length), ODSTEP);
		}
	};

	const stop = () => {
		window.clearInterval(timer);
		timer = null;
	};

	feed.addEventListener('mouseenter', stop);
	feed.addEventListener('mouseleave', start);
	feed.addEventListener('focusin', stop);
	feed.addEventListener('focusout', start);

	document.addEventListener('visibilitychange', () => {
		if (document.hidden) {
			stop();
		} else {
			start();
		}
	});

	start();
}
