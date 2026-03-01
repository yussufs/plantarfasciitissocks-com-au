<script>
  let { rows } = $props();
</script>

<div class="brand-product-accordions">
  {#each rows as row}
    <details>
      <summary>{row.title}</summary>
      <p>{row.content}</p>
    </details>
  {/each}
</div>

<style>
  .brand-product-accordions {
    @apply mt-6 border-t border-zinc-300;
  }

  .brand-product-accordions details {
    @apply border-b border-zinc-300;
  }

  .brand-product-accordions summary {
    @apply cursor-pointer py-4 text-2xl font-extrabold text-zinc-900;
    list-style: none;
  }

  .brand-product-accordions summary::-webkit-details-marker {
    display: none;
  }

  .brand-product-accordions summary::marker {
    content: '';
  }

  .brand-product-accordions summary::after {
    content: '+';
    float: right;
    @apply text-zinc-500;
  }

  .brand-product-accordions details[open] summary::after {
    content: '−';
  }

  .brand-product-accordions p {
    @apply pb-4 text-lg leading-relaxed text-zinc-700;
  }
</style>
