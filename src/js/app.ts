import '../css/app.css';
import '../css/woocommerce.css';
import { mount } from 'svelte';

import Example from '../components/Example.svelte';
import ProductStockBadge from '../components/ProductStockBadge.svelte';
import ProductSaleChip from '../components/ProductSaleChip.svelte';
import ProductBenefits from '../components/ProductBenefits.svelte';
import ProductColorSwatches from '../components/ProductColorSwatches.svelte';
import ProductBundleOptions from '../components/ProductBundleOptions.svelte';
import BuyNowButton from '../components/BuyNowButton.svelte';
import ProductPaymentBadges from '../components/ProductPaymentBadges.svelte';
import ProductDeliveryWindow from '../components/ProductDeliveryWindow.svelte';
import ProductTestimonial from '../components/ProductTestimonial.svelte';
import ProductAccordions from '../components/ProductAccordions.svelte';

function mountComponent(id: string, Component: Parameters<typeof mount>[0]): void {
  const el = document.getElementById(id);
  if (!el) return;
  mount(Component, {
    target: el,
    props: JSON.parse(el.dataset.config || '{}'),
  });
}

mountComponent('example-component', Example);

// Product page components — each receives its data via data-config from PHP.
mountComponent('brand-stock-badge', ProductStockBadge);
mountComponent('brand-sale-chip', ProductSaleChip);
mountComponent('brand-benefits', ProductBenefits);
mountComponent('brand-color-swatches', ProductColorSwatches);
mountComponent('brand-bundle-options', ProductBundleOptions);
mountComponent('brand-buy-now', BuyNowButton);
mountComponent('brand-payment-badges', ProductPaymentBadges);
mountComponent('brand-delivery-window', ProductDeliveryWindow);
mountComponent('brand-testimonial', ProductTestimonial);
mountComponent('brand-accordions', ProductAccordions);

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
