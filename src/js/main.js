import '../css/tailwind.css';
import '../scss/main.scss';

import { initMobileMenu } from './app/mobileMenu.js';
import { initHeroVideo } from './app/heroVideo.js';
import { initAccordion } from './app/accordion.js';
import { initTabs } from './app/tabs.js';
import { initContactForm } from './app/contactForm.js';
import { initVerifyBox } from './app/verifyBox.js';

document.addEventListener('DOMContentLoaded', () => {
	initMobileMenu();
	initHeroVideo();
	initAccordion();
	initTabs();
	initVerifyBox();
	initContactForm();
});
