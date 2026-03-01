<script>
  let { label = 'BUY IT NOW' } = $props();
</script>

<button type="submit" name="brand_buy_now" value="1" class="brand-buy-now-button">
  {label}
</button>

<style>
  .brand-buy-now-button {
    @apply mt-3 w-full rounded-lg border-2 border-red-500 bg-white py-3 text-xl font-black uppercase tracking-[0.04em] text-red-600 transition hover:bg-red-50;
  }
</style>
