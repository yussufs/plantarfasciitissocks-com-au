<script>
  import ColorSwatches from './ColorSwatches.svelte';
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
   *   bundleTiers: Array<any>,
   *   wcAjaxUrl: string,
   *   cartUrl: string,
   *   checkoutUrl: string,
   *   nonce: string
   * }}
   */
  let {
    productId,
    productType = 'simple',
    regularPrice = 0,
    salePrice = null,
    activePrice = 0,
    currencySymbol = '$',
    variations = [],
    colorAttributes = {},
    bundleTiers = [],
    wcAjaxUrl = '',
    cartUrl = '',
    checkoutUrl = '',
    nonce = '',
  } = $props();

  let selectedBundleIndex = $state(0);
  let selectedAttributes = $state({});
  let isAddingToCart = $state(false);
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

  // Bundle calculations.
  let selectedTier = $derived(bundleTiers[selectedBundleIndex] || bundleTiers[0]);

  let bundleUnitPrice = $derived(
    currentUnitPrice * (1 - (selectedTier?.discount || 0) / 100)
  );

  let totalPrice = $derived(
    (bundleUnitPrice * (selectedTier?.qty || 1)).toFixed(2)
  );

  let totalComparePrice = $derived(
    (currentRegularPrice * (selectedTier?.qty || 1)).toFixed(2)
  );

  let showCompare = $derived(
    hasDiscount || (selectedTier?.discount || 0) > 0
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

    const qty = selectedTier?.qty || 1;
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
      body.append('product_id', String(productId));
      body.append('quantity', String(qty));
      if (variationId) {
        body.append('variation_id', String(variationId));
        // Add variation attributes.
        for (const [attr, value] of Object.entries(selectedAttributes)) {
          body.append(`attribute_${attr}`, value);
        }
      }

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

      cartMessage = 'Added to cart!';
      cartMessageType = 'success';
      setTimeout(() => { cartMessage = ''; }, 3000);
    } catch {
      cartMessage = 'Something went wrong. Please try again.';
      cartMessageType = 'error';
    } finally {
      isAddingToCart = false;
    }
  }
</script>

<div class="space-y-5">
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

  <!-- Bundle selector -->
  {#if bundleTiers.length > 1}
    <BundleSelector
      tiers={bundleTiers}
      unitPrice={currentUnitPrice}
      {currencySymbol}
      {selectedBundleIndex}
      onselect={(i) => { selectedBundleIndex = i; }}
    />
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

  <!-- Cart toast -->
  {#if cartMessage}
    <div
      class="cart-toast"
      class:cart-toast-success={cartMessageType === 'success'}
      class:cart-toast-error={cartMessageType === 'error'}
      role="alert"
    >
      <div class="flex items-center gap-2">
        {#if cartMessageType === 'success'}
          <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
        {:else}
          <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
        {/if}
        <span class="text-sm font-medium">{cartMessage}</span>
      </div>
      {#if cartMessageType === 'success'}
        <a href={cartUrl} class="cart-toast-link">View Cart &rarr;</a>
      {/if}
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

  .cart-toast-success {
    background-color: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
  }

  .cart-toast-error {
    background-color: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
  }

  .cart-toast-link {
    flex-shrink: 0;
    font-weight: 600;
    text-decoration: underline;
    text-underline-offset: 2px;
    white-space: nowrap;
  }

  .cart-toast-link:hover {
    text-decoration-thickness: 2px;
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
