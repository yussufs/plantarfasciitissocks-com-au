<script>
  /**
   * @type {{
   *   attributeName: string,
   *   options: Array<{slug: string, label: string}>,
   *   selected: string,
   *   onselect: (slug: string) => void
   * }}
   */
  let { attributeName, options = [], selected = '', onselect } = $props();

  let displayName = $derived(
    attributeName
      .replace(/^pa_/, '')
      .replace(/[-_]/g, ' ')
      .replace(/\b\w/g, c => c.toUpperCase())
  );
</script>

<div>
  <p class="mb-2 text-sm font-medium text-zinc-700">
    {displayName}: <span class="font-semibold text-zinc-900">{options.find(o => o.slug === selected)?.label || ''}</span>
  </p>
  <div class="flex flex-wrap gap-2">
    {#each options as option}
      <button
        type="button"
        class="attribute-pill"
        class:is-selected={option.slug === selected}
        onclick={() => onselect(option.slug)}
      >
        {option.label}
      </button>
    {/each}
  </div>
</div>
