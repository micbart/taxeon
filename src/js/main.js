import '../css/tailwind.css';
import '../scss/main.scss';

import { initMobileMenu } from './app/mobileMenu.js';
import { initHeroVideo } from './app/heroVideo.js';
import { initTabs } from './app/tabs.js';
import { initVideos } from './app/videos.js';
import { initBooking } from './app/booking.js';
import { initTrial } from './app/trial.js';
import { initReviews } from './app/reviews.js';
import { initMatrix } from './app/matrix.js';
import { initKalkulatory } from './app/kalkulatory.js';
import { initContactForm } from './app/contactForm.js';
import { initVerifyBox } from './app/verifyBox.js';
import { initReveal } from './app/reveal.js';
import { initFaq } from './app/faq.js';

document.addEventListener('DOMContentLoaded', () => {
	initMobileMenu();
	initHeroVideo();
	initTabs();
	initVideos();
	initBooking();
	initTrial();
	initReviews();
	initMatrix();
	initKalkulatory();
	initVerifyBox();
	initContactForm();
	initReveal();
	initFaq();
});
