<script>
  /** @type {{ title?: string, initialCount?: number }} */
  let { title = 'Svelte Component', initialCount = 0 } = $props();

  let count = $state(initialCount);
  let doubled = $derived(count * 2);
</script>

<div class="rounded-lg border border-gray-200 bg-gray-50 p-6">
  <h3 class="mb-4 text-lg font-semibold text-gray-900">{title}</h3>

  <p class="mb-4 text-sm text-gray-600">
    Count: <span class="font-mono font-bold text-brand-600">{count}</span>
    &middot; Doubled: <span class="font-mono font-bold text-brand-600">{doubled}</span>
  </p>

  <div class="flex gap-2">
    <button
      class="btn btn-primary"
      onclick={() => count++}
    >
      Increment
    </button>

    <button
      class="btn btn-secondary"
      onclick={() => count = 0}
    >
      Reset
    </button>
  </div>
</div>
