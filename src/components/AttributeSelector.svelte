<script>
  /**
   * @type {{
   *   attributeName: string,
   *   options: Array<{slug: string, label: string}>,
   *   selected: string,
   *   onselect: (slug: string) => void,
   *   onSizeGuide?: (() => void) | null
   * }}
   */
  let { attributeName, options = [], selected = '', onselect, onSizeGuide = null } = $props();

  let displayName = $derived(
    attributeName
      .replace(/^pa_/, '')
      .replace(/[-_]/g, ' ')
      .replace(/\b\w/g, c => c.toUpperCase())
  );
</script>

<div>
  <div class="mb-2 flex items-center justify-between gap-3">
    <p class="text-sm font-medium text-zinc-700">
      {displayName}: <span class="font-semibold text-zinc-900">{options.find(o => o.slug === selected)?.label || ''}</span>
    </p>
    {#if onSizeGuide}
      <button
        type="button"
        class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700 hover:underline"
        onclick={onSizeGuide}
      >
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path d="M3 7h18v10H3z"/><path d="M7 7v3M11 7v5M15 7v3M19 7v5"/>
        </svg>
        Size guide
      </button>
    {/if}
  </div>
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
