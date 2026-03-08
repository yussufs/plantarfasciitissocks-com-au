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
        detail: { productName: name, qty: selectedTier?.qty || 1 },
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
