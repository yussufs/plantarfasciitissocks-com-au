<script>
  import ColorSwatches from './ColorSwatches.svelte';
  import AttributeSelector from './AttributeSelector.svelte';
  import BundleSelector from './BundleSelector.svelte';

  /**
   * @type {{
   *   productId: number,
   *   productType: string,
   *   regularPrice: number,
   *   salePrice: number|null,
   *   activePrice: number,
   *   currencySymbol: string,
   *   variations: Array<any>,
   *   colorAttributes: Record<string, Array<any>>,
   *   selectAttributes: Record<string, Array<any>>,
   *   bundleTiers: Array<any>,
   *   wcAjaxUrl: string,
   *   name: string,
   *   cartUrl: string,
   *   checkoutUrl: string,
   *   nonce: string
   * }}
   */
  let {
    productId,
    name = '',
    productType = 'simple',
    regularPrice = 0,
    salePrice = null,
    activePrice = 0,
    currencySymbol = '$',
    variations = [],
    colorAttributes = {},
    selectAttributes = {},
    sizeGuide = null,
    bundleTiers = [],
    wcAjaxUrl = '',
    cartUrl = '',
    checkoutUrl = '',
    nonce = '',
  } = $props();

  let selectedBundleIndex = $state(0);
  let selectedAttributes = $state({});
  let isAddingToCart = $state(false);
  let showSizeGuide = $state(false);

  const isSizeAttr = (name) => /size/i.test(name);

  // Close the size guide on Escape and lock body scroll while it's open.
  $effect(() => {
    if (!showSizeGuide) return;
    function onKeydown(e) {
      if (e.key === 'Escape') showSizeGuide = false;
    }
    window.addEventListener('keydown', onKeydown);
    document.body.style.overflow = 'hidden';
    return () => {
      window.removeEventListener('keydown', onKeydown);
      document.body.style.overflow = '';
    };
  });
  let cartMessage = $state('');
  let cartMessageType = $state('success');

  // Initialize selected attributes to first option.
  $effect(() => {
    const initial = {};
    for (const [attrName, swatches] of Object.entries(colorAttributes)) {
      if (swatches.length > 0) {
        initial[attrName] = swatches[0].slug;
      }
    }
    for (const [attrName, options] of Object.entries(selectAttributes)) {
      if (options.length > 0) {
        initial[attrName] = options[0].slug;
      }
    }
    selectedAttributes = initial;
  });

  // Find matching variation based on selected attributes.
  let matchedVariation = $derived.by(() => {
    if (productType !== 'variable' || variations.length === 0) return null;

    return variations.find(v => {
      return Object.entries(selectedAttributes).every(([attr, value]) => {
        const varAttrKey = `attribute_${attr}`;
        // Empty string in variation means "any".
        return !v.attributes[varAttrKey] || v.attributes[varAttrKey] === value;
      });
    }) || null;
  });

  // Current unit price (from variation or base).
  let currentUnitPrice = $derived(
    matchedVariation
      ? matchedVariation.sale_price || matchedVariation.regular_price
      : activePrice
  );

  let currentRegularPrice = $derived(
    matchedVariation ? matchedVariation.regular_price : regularPrice
  );

  let hasDiscount = $derived(currentUnitPrice < currentRegularPrice);

  // Products with bundle tiers (socks) show the bundle picker; everything else
  // shows a plain quantity stepper.
  let hasBundles = $derived(bundleTiers.length > 1);
  let selectedTier = $derived(hasBundles ? (bundleTiers[selectedBundleIndex] || bundleTiers[0]) : null);

  // Quantity for non-bundle products.
  let quantity = $state(1);

  let effectiveQty = $derived(hasBundles ? (selectedTier?.qty || 1) : quantity);

  // Fixed bundle total when a tier is selected; otherwise unit price × quantity.
  let totalPrice = $derived(
    (hasBundles && selectedTier
      ? Number(selectedTier.price)
      : currentUnitPrice * effectiveQty
    ).toFixed(2)
  );

  // Strikethrough = same quantity at the regular list price.
  let totalComparePrice = $derived(
    (currentRegularPrice * effectiveQty).toFixed(2)
  );

  let showCompare = $derived(
    Number(totalComparePrice) > Number(totalPrice)
  );

  // Dispatch variation change event when attributes change.
  $effect(() => {
    if (matchedVariation?.image) {
      window.dispatchEvent(new CustomEvent('product:variation-changed', {
        detail: { image: matchedVariation.image, variationId: matchedVariation.id },
      }));
    }
  });

  // Hide the static PHP price on mount.
  $effect(() => {
    const staticPrice = document.getElementById('product-price-static');
    if (staticPrice) {
      staticPrice.style.display = 'none';
    }
  });

  function selectAttribute(attrName, slug) {
    selectedAttributes = { ...selectedAttributes, [attrName]: slug };
  }

  async function addToCart(redirect = false) {
    if (isAddingToCart) return;

    const qty = Math.max(1, parseInt(effectiveQty, 10) || 1);
    const variationId = matchedVariation?.id || 0;

    // For variable products, a variation must be selected.
    if (productType === 'variable' && !variationId) {
      cartMessage = 'Please select all options.';
      cartMessageType = 'error';
      return;
    }

    isAddingToCart = true;
    cartMessage = '';

    try {
      const url = wcAjaxUrl.replace('%%endpoint%%', 'add_to_cart');

      const body = new URLSearchParams();
      // WC's wc-ajax=add_to_cart reads product_id and detects if it's a
      // variation automatically — it does NOT read a separate variation_id field.
      body.append('product_id', String(variationId || productId));
      body.append('quantity', String(qty));

      const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
      });

      const data = await res.json();

      if (data.error) {
        cartMessage = typeof data.error === 'string' ? data.error : 'Could not add to cart.';
        cartMessageType = 'error';
        return;
      }

      // Update WC fragments if available.
      if (data.fragments) {
        for (const [selector, html] of Object.entries(data.fragments)) {
          const els = document.querySelectorAll(selector);
          els.forEach(el => { el.outerHTML = html; });
        }
      }

      if (redirect) {
        window.location.href = checkoutUrl;
        return;
      }

      // Dispatch event for the cart drawer
      window.dispatchEvent(new CustomEvent('cart:item-added', {
        detail: { productName: name, qty },
      }));
    } catch {
      cartMessage = 'Something went wrong. Please try again.';
      cartMessageType = 'error';
    } finally {
      isAddingToCart = false;
    }
  }
</script>

<div class="space-y-3 lg:space-y-5">
  <!-- Reactive price display -->
  <div class="flex items-center gap-3">
    <span class="product-price">{currencySymbol}{totalPrice}</span>
    {#if showCompare}
      <span class="product-price-compare">{currencySymbol}{totalComparePrice}</span>
    {/if}
  </div>

  <!-- Color swatches -->
  {#each Object.entries(colorAttributes) as [attrName, swatches]}
    <ColorSwatches
      attributeName={attrName}
      {swatches}
      selected={selectedAttributes[attrName] || ''}
      onselect={(slug) => selectAttribute(attrName, slug)}
    />
  {/each}

  <!-- Other attributes (size, etc.) -->
  {#each Object.entries(selectAttributes) as [attrName, options]}
    <AttributeSelector
      attributeName={attrName}
      {options}
      selected={selectedAttributes[attrName] || ''}
      onselect={(slug) => selectAttribute(attrName, slug)}
      onSizeGuide={sizeGuide && isSizeAttr(attrName) ? () => (showSizeGuide = true) : null}
    />
  {/each}

  <!-- Bundle selector (socks) or quantity stepper (everything else) -->
  {#if hasBundles}
    <BundleSelector
      tiers={bundleTiers}
      unitPrice={currentUnitPrice}
      {currencySymbol}
      selectedIndex={selectedBundleIndex}
      onselect={(i) => { selectedBundleIndex = i; }}
    />
  {:else}
    <div>
      <p class="mb-2 text-sm font-medium text-zinc-700">Quantity</p>
      <div class="inline-flex items-center rounded-md border border-zinc-300">
        <button
          type="button"
          class="flex h-10 w-10 items-center justify-center text-lg text-zinc-700 hover:bg-zinc-100 disabled:opacity-40"
          onclick={() => (quantity = Math.max(1, quantity - 1))}
          disabled={quantity <= 1}
          aria-label="Decrease quantity"
        >&minus;</button>
        <input
          type="number"
          min="1"
          inputmode="numeric"
          class="h-10 w-14 border-x border-zinc-300 text-center text-sm font-medium text-zinc-900 focus:outline-none"
          bind:value={quantity}
          onchange={() => { quantity = Math.max(1, parseInt(quantity, 10) || 1); }}
          aria-label="Quantity"
        />
        <button
          type="button"
          class="flex h-10 w-10 items-center justify-center text-lg text-zinc-700 hover:bg-zinc-100"
          onclick={() => (quantity = (parseInt(quantity, 10) || 0) + 1)}
          aria-label="Increase quantity"
        >+</button>
      </div>
    </div>
  {/if}

  <!-- CTA buttons -->
  <div class="space-y-3">
    <button
      type="button"
      class="btn-add-to-cart"
      disabled={isAddingToCart}
      onclick={() => addToCart(false)}
    >
      {#if isAddingToCart}
        <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        Adding...
      {:else}
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="9" cy="21" r="1"></circle>
          <circle cx="20" cy="21" r="1"></circle>
          <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"></path>
        </svg>
        Add to Cart
      {/if}
    </button>
  </div>

  <!-- Error toast (success is handled by CartDrawer) -->
  {#if cartMessage && cartMessageType === 'error'}
    <div class="cart-toast cart-toast-error" role="alert">
      <div class="flex items-center gap-2">
        <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
        <span class="text-sm font-medium">{cartMessage}</span>
      </div>
    </div>
  {/if}

  <!-- Size guide modal -->
  {#if showSizeGuide && sizeGuide}
    <div
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
      role="dialog"
      aria-modal="true"
      aria-label="Size guide"
      tabindex="-1"
      onclick={() => (showSizeGuide = false)}
      onkeydown={(e) => { if (e.key === 'Escape') showSizeGuide = false; }}
    >
      <div
        class="relative max-h-[90vh] w-full max-w-lg overflow-auto rounded-xl bg-white p-4 shadow-xl sm:p-6"
        role="document"
        onclick={(e) => e.stopPropagation()}
      >
        <button
          type="button"
          class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-zinc-600 transition-colors hover:bg-zinc-200"
          onclick={() => (showSizeGuide = false)}
          aria-label="Close size guide"
        >
          <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 6l12 12M18 6L6 18" />
          </svg>
        </button>

        <h2 class="mb-3 pr-8 text-lg font-bold text-zinc-900">Size Guide</h2>
        <img src={sizeGuide.image} alt="Size guide" class="w-full rounded-lg" />
        <div class="mt-4 space-y-1.5 text-sm text-zinc-700">
          {#each sizeGuide.rows as row}
            <p>{row}</p>
          {/each}
        </div>
      </div>
    </div>
  {/if}
</div>

<style>
  .cart-toast {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    animation: toast-slide-down 0.3s ease-out;
  }

  .cart-toast-error {
    background-color: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
  }

@keyframes toast-slide-down {
    from {
      opacity: 0;
      transform: translateY(-0.5rem);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>
