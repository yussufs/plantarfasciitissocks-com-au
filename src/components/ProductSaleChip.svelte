<script>
  let { percentage } = $props();
</script>

<span class="brand-product-save-chip">
  <span class="brand-product-save-icon" aria-hidden="true"></span>
  SAVE {percentage}%
</span>

<style>
  .brand-product-save-chip {
    @apply mt-1 ml-2 inline-flex items-center gap-2 rounded-md bg-red-500 px-3 py-1 text-sm font-extrabold uppercase tracking-[0.04em] text-white;
  }

  .brand-product-save-icon {
    @apply inline-block h-2.5 w-2.5 rounded-full bg-white/90;
  }
</style>
