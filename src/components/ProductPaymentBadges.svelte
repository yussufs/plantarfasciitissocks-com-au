<script>
  let { methods, ariaLabel = 'Accepted payment methods' } = $props();
</script>

<div class="brand-payment-row" aria-label={ariaLabel}>
  {#each methods as method}
    <span class="brand-payment-chip">{method}</span>
  {/each}
</div>

<style>
  .brand-payment-row {
    @apply mt-5 flex flex-wrap gap-2;
  }

  .brand-payment-chip {
    @apply inline-flex min-h-[2rem] items-center rounded-md border border-zinc-300 bg-white px-2.5 text-sm font-semibold text-zinc-700;
  }
</style>
