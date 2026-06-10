<script>
  /**
   * @type {{
   *   tiers: Array<{qty: number, price: number, label: string, badge: string}>,
   *   unitPrice: number,
   *   currencySymbol: string,
   *   selectedIndex: number,
   *   onselect: (index: number) => void
   * }}
   */
  let { tiers = [], unitPrice = 0, currencySymbol = '$', selectedIndex = 0, onselect } = $props();

  // Fixed bundle total for the tier.
  function tierPrice(tier) {
    return Number(tier.price).toFixed(2);
  }

  function tierUnitPrice(tier) {
    return (Number(tier.price) / tier.qty).toFixed(2);
  }

  // "Regular" total to compare against: an explicit per-tier `regular` price if
  // provided (e.g. the foot massager), otherwise the product's unit price × qty.
  function tierRegular(tier) {
    return tier.regular != null ? Number(tier.regular) : unitPrice * tier.qty;
  }

  // Saving vs the regular total above.
  function savings(tier) {
    return (tierRegular(tier) - Number(tier.price)).toFixed(2);
  }

  function percentOff(tier) {
    const full = tierRegular(tier);
    if (full <= 0) return 0;
    return Math.round((1 - Number(tier.price) / full) * 100);
  }
</script>

<div class="space-y-2">
  <p class="text-sm font-semibold text-zinc-900">Bundle & Save</p>
  {#each tiers as tier, i}
    <button
      type="button"
      class="bundle-option"
      class:is-selected={i === selectedIndex}
      onclick={() => onselect(i)}
    >
      {#if tier.badge}
        <span class="bundle-option-badge">{tier.badge}</span>
      {/if}

      <span class="bundle-option-radio">
        <span class="bundle-option-radio-dot"></span>
      </span>

      <span class="flex flex-1 items-center justify-between">
        <span>
          <span class="text-sm font-semibold text-zinc-900">{tier.label}</span>
          {#if percentOff(tier) > 0}
            <span class="ml-1.5 text-xs font-bold text-brand-600">{percentOff(tier)}% OFF</span>
          {/if}
        </span>
        <span class="text-right">
          <span>
            <span class="text-sm font-bold text-zinc-900">{currencySymbol}{tierPrice(tier)}</span>
            {#if tierRegular(tier) > Number(tier.price)}
              <span class="ml-1 text-xs text-zinc-400 line-through">{currencySymbol}{tierRegular(tier).toFixed(2)}</span>
            {/if}
          </span>
          {#if tier.qty > 1}
            <span class="block text-xs text-zinc-500">{currencySymbol}{tierUnitPrice(tier)} each</span>
          {/if}
          {#if savings(tier) > 0}
            <span class="block text-xs text-green-600">Save {currencySymbol}{savings(tier)}</span>
          {/if}
        </span>
      </span>
    </button>
  {/each}
</div>
