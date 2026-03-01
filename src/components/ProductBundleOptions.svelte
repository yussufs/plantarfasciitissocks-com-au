<script>
  import { onMount } from 'svelte';

  let { options, title = 'BUNDLE & SAVE', mostPopularLabel = 'Most Popular' } = $props();

  let selectedQty = $state(options[0]?.quantity ?? 1);
  let quantityInput;

  function handleChange() {
    if (quantityInput) {
      quantityInput.value = String(selectedQty);
    }
  }

  onMount(() => {
    const form = document.querySelector('form.cart');
    if (form) {
      quantityInput = form.querySelector('input.qty[name="quantity"]');
      const wrapper = quantityInput?.closest('.quantity');
      if (wrapper) wrapper.classList.add('is-bundle-controlled');
    }
    handleChange();
  });
</script>

<div class="brand-bundle">
  <p class="brand-bundle-title"><span>{title}</span></p>

  {#each options as option}
    <label class="brand-bundle-option" class:is-active={selectedQty === option.quantity}>
      <input
        type="radio"
        name="brand_bundle_qty"
        bind:group={selectedQty}
        value={option.quantity}
        onchange={handleChange}
      />

      {#if option.isPopular}
        <span class="brand-bundle-popular">{mostPopularLabel}</span>
      {/if}

      <span class="brand-bundle-copy">
        <strong>{option.buyLabel}</strong>
        <small>{@html option.pricePerUnit} {option.perUnitLabel}</small>
      </span>

      <span class="brand-bundle-pricing">
        <strong>{@html option.totalPrice}</strong>
        {#if option.totalRegular}
          <s>{@html option.totalRegular}</s>
        {/if}
      </span>
    </label>
  {/each}
</div>

<style>
  .brand-bundle {
    @apply mt-6;
  }

  .brand-bundle-title {
    @apply relative mb-4 text-center text-lg font-black uppercase tracking-[0.05em] text-zinc-900;
  }

  .brand-bundle-title::before,
  .brand-bundle-title::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 32%;
    border-top: 2px solid rgb(239 43 45 / 0.95);
  }

  .brand-bundle-title::before {
    left: 0;
  }

  .brand-bundle-title::after {
    right: 0;
  }

  .brand-bundle-title span {
    @apply bg-zinc-100 px-3;
  }

  .brand-bundle-option {
    @apply relative mb-3 flex cursor-pointer items-start justify-between rounded-xl border border-red-300 bg-white p-4 transition hover:border-red-500;
  }

  .brand-bundle-option input {
    @apply mt-1 mr-3 h-5 w-5 accent-red-500;
  }

  .brand-bundle-option.is-active {
    @apply border-2 border-red-500 bg-red-50;
  }

  .brand-bundle-copy {
    @apply mr-auto flex flex-col;
  }

  .brand-bundle-copy strong {
    @apply text-2xl font-extrabold text-zinc-900;
  }

  .brand-bundle-copy small {
    @apply text-xl text-zinc-700;
  }

  .brand-bundle-pricing {
    @apply ml-4 flex flex-col items-end text-right;
  }

  .brand-bundle-pricing strong {
    @apply text-2xl font-extrabold text-red-600;
  }

  .brand-bundle-pricing s {
    @apply text-xl text-zinc-500;
  }

  .brand-bundle-popular {
    @apply absolute -top-3 right-3 rounded-sm bg-red-500 px-2 py-0.5 text-sm font-extrabold uppercase tracking-[0.04em] text-white;
  }
</style>
