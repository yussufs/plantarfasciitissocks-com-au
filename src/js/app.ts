import '../css/app.css';
import { mount } from 'svelte';
import Example from '../components/Example.svelte';

const exampleEl = document.getElementById('example-component');
if (exampleEl) {
  const config = JSON.parse(exampleEl.dataset.config || '{}');
  mount(Example, {
    target: exampleEl,
    props: config,
  });
}

// ─── Mobile nav ───────────────────────────────────────────────────────────────

const mobileNavToggle = document.querySelector<HTMLButtonElement>('[data-mobile-nav-toggle]');
const mobileNavPanel = document.getElementById('mobile-site-nav');
const mobileNavOverlay = document.querySelector<HTMLElement>('[data-mobile-nav-overlay]');
const mobileNavClose = document.querySelector<HTMLButtonElement>('[data-mobile-nav-close]');

if (mobileNavToggle && mobileNavPanel && mobileNavOverlay) {
  const mobileDrawerTransitionMs = 320;

  const setMobileNavOpen = (isOpen: boolean) => {
    mobileNavToggle.setAttribute('aria-expanded', String(isOpen));
    mobileNavToggle.classList.toggle('is-open', isOpen);
    document.body.classList.toggle('mobile-nav-open', isOpen);

    if (isOpen) {
      mobileNavOverlay.setAttribute('aria-hidden', 'false');
      mobileNavPanel.hidden = false;
      mobileNavOverlay.hidden = false;
      window.requestAnimationFrame(() => {
        mobileNavPanel.classList.add('is-open');
        mobileNavOverlay.classList.add('is-open');
      });
      return;
    }

    mobileNavPanel.classList.remove('is-open');
    mobileNavOverlay.classList.remove('is-open');
    mobileNavOverlay.setAttribute('aria-hidden', 'true');
    window.setTimeout(() => {
      const stillClosed = mobileNavToggle.getAttribute('aria-expanded') === 'false';
      if (stillClosed) {
        mobileNavPanel.hidden = true;
        mobileNavOverlay.hidden = true;
      }
    }, mobileDrawerTransitionMs);
  };

  mobileNavPanel.hidden = true;
  mobileNavOverlay.hidden = true;
  mobileNavPanel.classList.remove('is-open');
  mobileNavOverlay.classList.remove('is-open');
  mobileNavToggle.setAttribute('aria-expanded', 'false');
  mobileNavOverlay.setAttribute('aria-hidden', 'true');
  document.body.classList.remove('mobile-nav-open');

  mobileNavToggle.addEventListener('click', () => {
    const isExpanded = mobileNavToggle.getAttribute('aria-expanded') === 'true';
    setMobileNavOpen(!isExpanded);
  });

  mobileNavOverlay.addEventListener('click', () => setMobileNavOpen(false));

  if (mobileNavClose) {
    mobileNavClose.addEventListener('click', () => setMobileNavOpen(false));
  }

  mobileNavPanel.querySelectorAll<HTMLAnchorElement>('a').forEach((link) => {
    link.addEventListener('click', () => setMobileNavOpen(false));
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      setMobileNavOpen(false);
    }
  });

  const desktopBreakpoint = window.matchMedia('(min-width: 1024px)');
  desktopBreakpoint.addEventListener('change', (event) => {
    if (event.matches) {
      setMobileNavOpen(false);
    }
  });
}
