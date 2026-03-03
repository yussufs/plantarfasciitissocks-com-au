<script>
  import EmblaCarousel from 'embla-carousel';

  /** @type {{ images: Array<{id: number, src: string, thumb: string, alt: string}> }} */
  let { images = [] } = $props();

  let activeIndex = $state(0);
  let viewportEl = $state(null);
  let thumbsEl = $state(null);

  /** @type {ReturnType<typeof EmblaCarousel> | null} */
  let emblaApi = null;

  // Initialize Embla when viewport mounts.
  $effect(() => {
    if (!viewportEl) return;

    emblaApi = EmblaCarousel(viewportEl, {
      loop: false,
      dragFree: false,
    });

    emblaApi.on('select', () => {
      activeIndex = emblaApi.selectedScrollSnap();
      scrollThumbIntoView(activeIndex);
    });

    return () => {
      emblaApi?.destroy();
      emblaApi = null;
    };
  });

  function selectImage(index) {
    if (emblaApi) {
      emblaApi.scrollTo(index);
    } else {
      activeIndex = index;
    }
  }

  function scrollThumbIntoView(index) {
    if (!thumbsEl) return;
    const thumb = thumbsEl.children[index];
    if (thumb) {
      thumb.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'smooth' });
    }
  }

  // Listen for variation change events from ProductOptions.
  $effect(() => {
    function handleVariationChange(e) {
      const { image } = e.detail;
      if (!image?.src) return;

      const existingIndex = images.findIndex(img => img.src === image.src);
      if (existingIndex >= 0) {
        selectImage(existingIndex);
      } else {
        // Prepend variation image and select it.
        images = [{ id: 0, src: image.src, thumb: image.thumb || image.src, alt: image.alt || '' }, ...images];

        // reInit Embla after DOM updates with the new slide.
        requestAnimationFrame(() => {
          if (emblaApi) {
            emblaApi.reInit();
            emblaApi.scrollTo(0, true);
            activeIndex = 0;
          }
        });
      }
    }

    window.addEventListener('product:variation-changed', handleVariationChange);
    return () => window.removeEventListener('product:variation-changed', handleVariationChange);
  });
</script>

<div class="product-gallery">
  <!-- Embla viewport -->
  <div class="product-gallery-viewport" bind:this={viewportEl}>
    <div class="product-gallery-container">
      {#each images as image, i}
        <div class="product-gallery-slide">
          <img
            src={image.src}
            alt={image.alt}
            loading={i === 0 ? 'eager' : 'lazy'}
            fetchpriority={i === 0 ? 'high' : undefined}
          />
        </div>
      {/each}
    </div>
  </div>

  <!-- Thumbnail strip -->
  {#if images.length > 1}
    <div class="product-gallery-thumbs">
      <div class="product-gallery-thumbs-scroll" bind:this={thumbsEl}>
        {#each images as image, i}
          <button
            type="button"
            class="product-gallery-thumb"
            class:is-active={i === activeIndex}
            onclick={() => selectImage(i)}
            aria-label="View image {i + 1}"
          >
            <img src={image.thumb} alt="" loading="lazy" />
          </button>
        {/each}
      </div>
    </div>
  {/if}
</div>
