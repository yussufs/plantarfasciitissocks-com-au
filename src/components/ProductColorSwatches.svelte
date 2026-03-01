<script>
  let { colors, label = 'Color' } = $props();

  let activeIndex = $state(0);
</script>

<div class="brand-product-colors">
  <p>{label}</p>
  <div class="brand-product-color-grid">
    {#each colors as color, i}
      <span
        class="brand-product-color"
        class:is-active={activeIndex === i}
        title={color.name}
        style="background:{color.background};"
        role="button"
        tabindex="0"
        onclick={() => (activeIndex = i)}
        onkeydown={(e) => e.key === 'Enter' && (activeIndex = i)}
      ></span>
    {/each}
  </div>
</div>

<style>
  .brand-product-colors {
    @apply mt-5;
  }

  .brand-product-colors p {
    @apply mb-2 text-lg font-bold text-zinc-900;
  }

  .brand-product-color-grid {
    @apply flex items-center gap-3;
  }

  .brand-product-color {
    @apply inline-block h-12 w-12 cursor-pointer rounded-full border-2 border-zinc-200 bg-zinc-200;
  }

  .brand-product-color.is-active {
    @apply border-zinc-800 ring-2 ring-white;
  }
</style>
