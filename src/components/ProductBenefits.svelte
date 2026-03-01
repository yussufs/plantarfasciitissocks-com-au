<script>
  let { benefits, icons = ['🧖', '🩸', '💪'] } = $props();
</script>

<ul class="brand-product-benefits">
  {#each benefits as benefit, i}
    <li>
      <span aria-hidden="true">{icons[i] ?? '•'}</span>
      {benefit}
    </li>
  {/each}
</ul>

<style>
  .brand-product-benefits {
    @apply mt-4 space-y-2 text-2xl font-medium text-zinc-700;
  }

  .brand-product-benefits li {
    @apply flex items-center gap-2;
  }
</style>
