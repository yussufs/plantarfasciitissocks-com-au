<script>
  let { badgeText } = $props();
</script>

<p class="brand-product-stock-badge">{badgeText}</p>

<style>
  .brand-product-stock-badge {
    @apply mb-4 inline-block border-2 border-red-500 px-3 py-1 text-sm font-extrabold uppercase tracking-[0.06em] text-red-600;
  }
</style>
