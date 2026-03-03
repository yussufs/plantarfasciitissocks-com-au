<script>
  /**
   * @type {{
   *   attributeName: string,
   *   swatches: Array<{slug: string, label: string, hex: string|null}>,
   *   selected: string,
   *   onselect: (slug: string) => void
   * }}
   */
  let { attributeName, swatches = [], selected = '', onselect } = $props();

  // Map common color names to hex when no custom hex is set.
  const colorMap = {
    black: '#000000',
    white: '#FFFFFF',
    red: '#DC2626',
    blue: '#2563EB',
    green: '#16A34A',
    yellow: '#EAB308',
    orange: '#EA580C',
    purple: '#9333EA',
    pink: '#EC4899',
    brown: '#92400E',
    grey: '#6B7280',
    gray: '#6B7280',
    navy: '#1E3A5F',
    beige: '#D2B48C',
    teal: '#0D9488',
    coral: '#F97316',
  };

  function getHex(swatch) {
    if (swatch.hex) return swatch.hex;
    const slug = swatch.slug.toLowerCase();
    return colorMap[slug] || '#9CA3AF';
  }
</script>

<div>
  <p class="mb-2 text-sm font-medium text-zinc-700">
    Colour: <span class="font-semibold text-zinc-900">{swatches.find(s => s.slug === selected)?.label || ''}</span>
  </p>
  <div class="flex flex-wrap gap-2">
    {#each swatches as swatch}
      <button
        type="button"
        class="color-swatch"
        class:is-selected={swatch.slug === selected}
        onclick={() => onselect(swatch.slug)}
        aria-label={swatch.label}
        title={swatch.label}
      >
        <span class="color-swatch-inner" style="background-color: {getHex(swatch)}"></span>
      </button>
    {/each}
  </div>
</div>
