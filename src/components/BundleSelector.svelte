<script>
  /**
   * @type {{
   *   tiers: Array<{qty: number, label: string, discount: number, badge: string}>,
   *   unitPrice: number,
   *   currencySymbol: string,
   *   selectedIndex: number,
   *   onselect: (index: number) => void
   * }}
   */
  let { tiers = [], unitPrice = 0, currencySymbol = '$', selectedIndex = 0, onselect } = $props();

  function tierPrice(tier) {
    const discounted = unitPrice * (1 - tier.discount / 100);
    return (discounted * tier.qty).toFixed(2);
  }

  function tierUnitPrice(tier) {
    return (unitPrice * (1 - tier.discount / 100)).toFixed(2);
  }

  function savings(tier) {
    return ((unitPrice * tier.qty) - parseFloat(tierPrice(tier))).toFixed(2);
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
          {#if tier.discount > 0}
            <span class="ml-1.5 text-xs font-bold text-brand-600">{tier.discount}% OFF</span>
          {/if}
        </span>
        <span class="text-right">
          <span class="text-sm font-bold text-zinc-900">{currencySymbol}{tierPrice(tier)}</span>
          {#if tier.qty > 1}
            <span class="block text-xs text-zinc-500">{currencySymbol}{tierUnitPrice(tier)} each</span>
          {/if}
          {#if tier.discount > 0}
            <span class="block text-xs text-green-600">Save {currencySymbol}{savings(tier)}</span>
          {/if}
        </span>
      </span>
    </button>
  {/each}
</div>
