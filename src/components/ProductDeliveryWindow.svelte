<script>
  let { text } = $props();
</script>

<p class="brand-delivery-note">
  <span aria-hidden="true">🚚</span>
  {text}
</p>

<style>
  .brand-delivery-note {
    @apply mt-5 flex items-start gap-2 text-xl font-medium leading-snug text-zinc-700;
  }
</style>
