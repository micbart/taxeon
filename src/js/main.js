import '../css/tailwind.css';
import '../scss/main.scss';

import { initStickyHeader } from './app/stickyHeader.js';
import { initMobileMenu } from './app/mobileMenu.js';
import { initReveal } from './app/reveal.js';
import { initScrollToTop } from './app/scrollToTop.js';
import { initHeroVideo } from './app/heroVideo.js';
import { initHeroBackground } from './app/heroBackground.js';
import { initAccordion } from './app/accordion.js';
import { initTabs } from './app/tabs.js';
import { initContactForm } from './app/contactForm.js';
import { initVerifyBox } from './app/verifyBox.js';
import { initTrialWizard } from './app/trialWizard.js';
import { initFilterPills } from './app/filterPills.js';
import { initTopbar } from './app/topbar.js';

document.addEventListener('DOMContentLoaded', () => {
	initStickyHeader();
	initMobileMenu();
	initReveal();
	initScrollToTop();
	initHeroVideo();
	initHeroBackground();
	initAccordion();
	initTabs();
	initVerifyBox();
	initContactForm();
	initTrialWizard();
	initFilterPills();
	initTopbar();
});
