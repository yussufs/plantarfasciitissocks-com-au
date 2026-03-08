<script>
  /**
   * Cart drawer notification — slides from right on desktop, top on mobile.
   * Listens for 'cart:item-added' custom DOM events.
   *
   * @type {{ cartUrl: string, checkoutUrl: string }}
   */
  let { cartUrl = '/cart/', checkoutUrl = '/checkout/' } = $props();

  let isOpen = $state(false);
  let addedProductName = $state('');
  let addedQty = $state(1);
  let cartItems = $state([]);
  let cartTotal = $state('');
  let isLoadingCart = $state(false);
  let autoCloseTimer = $state(null);

  function open(productName, qty) {
    addedProductName = productName;
    addedQty = qty;
    isOpen = true;
    fetchCart();

    // Auto-close after 6 seconds
    clearAutoClose();
    autoCloseTimer = setTimeout(() => { close(); }, 6000);
  }

  function close() {
    isOpen = false;
    clearAutoClose();
  }

  function clearAutoClose() {
    if (autoCloseTimer) {
      clearTimeout(autoCloseTimer);
      autoCloseTimer = null;
    }
  }

  async function fetchCart() {
    isLoadingCart = true;
    try {
      const res = await fetch('/wp-json/wc/store/v1/cart', {
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
      });
      if (res.ok) {
        const data = await res.json();
        cartItems = (data.items || []).map(item => ({
          key: item.key,
          name: item.name,
          qty: item.quantity,
          price: formatPrice(item.prices?.price, item.prices?.currency_minor_unit ?? 2),
          image: item.images?.[0]?.thumbnail || item.images?.[0]?.src || '',
        }));
        cartTotal = formatPrice(data.totals?.total_price, data.totals?.currency_minor_unit ?? 2);
      }
    } catch {
      // Silently fail — cart contents are nice-to-have
    } finally {
      isLoadingCart = false;
    }
  }

  function formatPrice(raw, decimals = 2) {
    if (!raw) return '';
    const num = parseInt(raw, 10) / Math.pow(10, decimals);
    return '$' + num.toFixed(2);
  }

  // Listen for add-to-cart events
  $effect(() => {
    function handleItemAdded(e) {
      const { productName = 'Item', qty = 1 } = e.detail || {};
      open(productName, qty);
    }
    window.addEventListener('cart:item-added', handleItemAdded);
    return () => window.removeEventListener('cart:item-added', handleItemAdded);
  });

  // Pause auto-close on hover
  function handleMouseEnter() {
    clearAutoClose();
  }

  function handleMouseLeave() {
    autoCloseTimer = setTimeout(() => { close(); }, 3000);
  }

  // Close on Escape
  $effect(() => {
    function handleKeydown(e) {
      if (e.key === 'Escape' && isOpen) close();
    }
    window.addEventListener('keydown', handleKeydown);
    return () => window.removeEventListener('keydown', handleKeydown);
  });
</script>

<!-- Overlay -->
{#if isOpen}
  <!-- svelte-ignore a11y_no_static_element_interactions -->
  <div
    class="cart-drawer-overlay fixed inset-0 z-[998] bg-black/30"
    onclick={close}
    onkeydown={() => {}}
  ></div>
{/if}

<!-- Drawer -->
<div
  class="cart-drawer fixed z-[999] flex flex-col overflow-hidden bg-white shadow-2xl
    max-md:inset-x-0 max-md:top-0 max-md:max-h-[85vh] max-md:rounded-b-2xl
    md:inset-y-0 md:right-0 md:w-96 md:max-w-full"
  class:cart-drawer-open={isOpen}
  role="dialog"
  aria-label="Cart notification"
  aria-hidden={!isOpen}
  onmouseenter={handleMouseEnter}
  onmouseleave={handleMouseLeave}
>
  <!-- Header -->
  <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4">
    <div class="flex items-center gap-2">
      <svg class="h-5 w-5 flex-shrink-0 text-brand-600" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
      </svg>
      <span class="text-sm font-semibold text-zinc-900">Added to cart</span>
    </div>
    <button
      type="button"
      class="flex h-8 w-8 items-center justify-center rounded-md border-none bg-transparent text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-900"
      onclick={close}
      aria-label="Close cart notification"
    >
      <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
      </svg>
    </button>
  </div>

  <!-- Added item highlight -->
  <div class="border-b border-brand-100 bg-brand-50 px-5 py-3">
    <p class="text-sm text-zinc-700">
      <span class="font-semibold text-zinc-900">{addedProductName}</span>
      {#if addedQty > 1}
        <span class="text-zinc-500">&times; {addedQty}</span>
      {/if}
    </p>
  </div>

  <!-- Cart contents -->
  <div class="flex-1 overflow-y-auto px-5 py-4">
    {#if isLoadingCart}
      <div class="flex items-center justify-center py-4">
        <svg class="h-5 w-5 animate-spin text-brand-400" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
      </div>
    {:else if cartItems.length > 0}
      <div class="mb-2 text-xs font-medium uppercase tracking-wide text-zinc-500">
        Your cart ({cartItems.reduce((sum, i) => sum + i.qty, 0)} {cartItems.reduce((sum, i) => sum + i.qty, 0) === 1 ? 'item' : 'items'})
      </div>
      <ul class="m-0 flex list-none flex-col gap-3 p-0">
        {#each cartItems as item (item.key)}
          <li class="flex items-center gap-3">
            {#if item.image}
              <img
                src={item.image}
                alt={item.name}
                class="h-12 w-12 flex-shrink-0 rounded-md bg-zinc-100 object-cover"
                loading="lazy"
              />
            {/if}
            <div class="min-w-0 flex-1">
              <p class="truncate text-sm font-medium text-zinc-900">{item.name}</p>
              <p class="text-xs text-zinc-500">Qty: {item.qty} &middot; {item.price}</p>
            </div>
          </li>
        {/each}
      </ul>
      {#if cartTotal}
        <div class="mt-3 flex items-center justify-between border-t border-zinc-100 pt-3">
          <span class="text-sm font-medium text-zinc-600">Total</span>
          <span class="text-sm font-bold text-zinc-900">{cartTotal}</span>
        </div>
      {/if}
    {/if}
  </div>

  <!-- Actions -->
  <div class="flex gap-2 border-t border-zinc-100 px-5 py-4">
    <a href={cartUrl} class="btn btn-secondary flex-1 text-center">View Cart</a>
    <a href={checkoutUrl} class="btn btn-primary flex-1 text-center">Checkout</a>
  </div>
</div>

<style>
  .cart-drawer-overlay {
    animation: fade-in 0.2s ease-out;
  }

  .cart-drawer {
    transition: transform 0.3s cubic-bezier(0.32, 0.72, 0, 1);
  }

  /* Mobile: slide from top */
  @media (max-width: 767px) {
    .cart-drawer {
      transform: translateY(-100%);
    }
    .cart-drawer-open {
      transform: translateY(0);
    }
  }

  /* Desktop: slide from right */
  @media (min-width: 768px) {
    .cart-drawer {
      transform: translateX(100%);
    }
    .cart-drawer-open {
      transform: translateX(0);
    }
  }

  @keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
  }
</style>
