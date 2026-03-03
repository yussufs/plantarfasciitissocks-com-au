<script>
  /** @type {{
   *   reviews: Array<{
   *     id: number,
   *     author: string,
   *     location: string,
   *     rating: number,
   *     text: string,
   *     image: { src: string, srcset: string, webp: string, alt: string } | null,
   *     featured: boolean,
   *     date: string
   *   }>,
   *   avgRating: number,
   *   reviewCount: number,
   *   productName: string
   * }} */
  let {
    reviews = [],
    avgRating = 5,
    reviewCount = 0,
    productName = '',
  } = $props();

  const INITIAL_VISIBLE = 4;

  let showAll = $state(false);
  let lightboxImage = $state(null);

  let visibleReviews = $derived(
    showAll ? reviews : reviews.slice(0, INITIAL_VISIBLE)
  );

  let hasMore = $derived(reviews.length > INITIAL_VISIBLE);

  function openLightbox(image) {
    lightboxImage = image;
  }

  function closeLightbox() {
    lightboxImage = null;
  }

  // Close lightbox on Escape key.
  $effect(() => {
    if (!lightboxImage) return;

    function onKeydown(e) {
      if (e.key === 'Escape') closeLightbox();
    }
    window.addEventListener('keydown', onKeydown);
    return () => window.removeEventListener('keydown', onKeydown);
  });

  // Lock body scroll when lightbox is open.
  $effect(() => {
    if (lightboxImage) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
  });

  function getInitials(name) {
    return name
      .split(/\s+/)
      .map(w => w[0])
      .join('')
      .toUpperCase()
      .slice(0, 2);
  }


</script>

<!-- Header -->
<div class="reviews-header">
  <h2 class="reviews-title">Customer Reviews</h2>
  <div class="reviews-summary">
    <div class="reviews-stars" aria-label="{avgRating} out of 5 stars">
      {#each Array(5) as _, i}
        {#if i < Math.floor(avgRating)}
          <svg class="reviews-star reviews-star--filled" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
          </svg>
        {:else if i < avgRating}
          <svg class="reviews-star reviews-star--filled" viewBox="0 0 24 24" aria-hidden="true">
            <defs>
              <linearGradient id="half-{i}">
                <stop offset="50%" stop-color="currentColor"/>
                <stop offset="50%" stop-color="transparent"/>
              </linearGradient>
            </defs>
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="url(#half-{i})" stroke="currentColor" stroke-width="1"/>
          </svg>
        {:else}
          <svg class="reviews-star reviews-star--empty" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
          </svg>
        {/if}
      {/each}
    </div>
    <span class="reviews-avg">{avgRating}</span>
    <span class="reviews-count">({reviewCount} {reviewCount === 1 ? 'review' : 'reviews'})</span>
  </div>
</div>

<!-- Review Grid -->
<div class="reviews-grid">
  {#each visibleReviews as review (review.id)}
    <div class="review-card">
      <!-- Stars -->
      <div class="review-stars" aria-label="{review.rating} out of 5 stars">
        {#each Array(5) as _, i}
          {#if i < review.rating}
            <svg class="review-star review-star--filled" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
          {:else}
            <svg class="review-star review-star--empty" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
          {/if}
        {/each}
      </div>

      <!-- Review text -->
      <p class="review-text">{review.text}</p>

      <!-- Review image -->
      {#if review.image}
        <button
          type="button"
          class="review-image-btn"
          onclick={() => openLightbox(review.image)}
          aria-label="View full image"
        >
          {#if review.image.webp}
            <picture>
              <source type="image/webp" srcset={review.image.webp} sizes="(min-width: 640px) 340px, calc(100vw - 4rem)">
              {#if review.image.srcset}
                <source srcset={review.image.srcset} sizes="(min-width: 640px) 340px, calc(100vw - 4rem)">
              {/if}
              <img src={review.image.src} alt="Photo from {review.author}" class="review-image" loading="lazy">
            </picture>
          {:else}
            <img src={review.image.src} alt="Photo from {review.author}" class="review-image" loading="lazy">
          {/if}
        </button>
      {/if}

      <!-- Author -->
      <div class="review-author">
        <div class="review-avatar" aria-hidden="true">
          {getInitials(review.author)}
        </div>
        <div class="review-author-info">
          <div class="review-author-name">
            {review.author}
            <span class="review-verified" title="Verified Purchase">
              <svg class="review-verified-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
              </svg>
              Verified
            </span>
          </div>
          {#if review.location}
            <div class="review-author-meta">{review.location}</div>
          {/if}
        </div>
      </div>
    </div>
  {/each}
</div>

<!-- Show more button -->
{#if hasMore && !showAll}
  <div class="reviews-show-more">
    <button type="button" class="reviews-show-more-btn" onclick={() => { showAll = true; }}>
      Show All {reviews.length} Reviews
    </button>
  </div>
{/if}

<!-- svelte-ignore a11y_no_static_element_interactions -->
<!-- Lightbox -->
{#if lightboxImage}
  <!-- svelte-ignore a11y_interactive_supports_focus -->
  <!-- svelte-ignore a11y_click_events_have_key_events -->
  <div
    class="lightbox-overlay"
    role="dialog"
    aria-modal="true"
    aria-label="Review image"
    tabindex="-1"
    onclick={closeLightbox}
  >
    <!-- svelte-ignore a11y_click_events_have_key_events -->
    <!-- svelte-ignore a11y_no_static_element_interactions -->
    <div class="lightbox-content" onclick={(e) => e.stopPropagation()}>
      <button type="button" class="lightbox-close" onclick={closeLightbox} aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
      {#if lightboxImage.webp}
        <picture>
          <source type="image/webp" srcset={lightboxImage.webp} sizes="90vw">
          {#if lightboxImage.srcset}
            <source srcset={lightboxImage.srcset} sizes="90vw">
          {/if}
          <img src={lightboxImage.src} alt="Customer review" class="lightbox-image">
        </picture>
      {:else}
        <img src={lightboxImage.src} alt="Customer review" class="lightbox-image">
      {/if}
    </div>
  </div>
{/if}

<style>
  /* ── Header ─────────────────────────────────── */

  .reviews-header {
    margin-bottom: 1.5rem;
  }

  .reviews-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #18181b;
    margin: 0 0 0.5rem;
  }

  .reviews-summary {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .reviews-stars {
    display: flex;
    gap: 0.125rem;
  }

  .reviews-star {
    width: 1.25rem;
    height: 1.25rem;
  }

  .reviews-star--filled {
    color: #f59e0b;
  }

  .reviews-star--empty {
    color: #d4d4d8;
  }

  .reviews-avg {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #3f3f46;
  }

  .reviews-count {
    font-size: 0.875rem;
    color: #71717a;
  }

  /* ── Grid ────────────────────────────────────── */

  .reviews-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
  }

  @media (min-width: 640px) {
    .reviews-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  /* ── Card ────────────────────────────────────── */

  .review-card {
    background: #fff;
    border: 1px solid #e4e4e7;
    border-radius: 0.75rem;
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .review-stars {
    display: flex;
    gap: 0.0625rem;
  }

  .review-star {
    width: 1rem;
    height: 1rem;
  }

  .review-star--filled {
    color: #f59e0b;
  }

  .review-star--empty {
    color: #d4d4d8;
  }

  .review-text {
    font-size: 0.9375rem;
    line-height: 1.6;
    color: #3f3f46;
    margin: 0;
  }

  /* ── Review image ───────────────────────────── */

  .review-image-btn {
    display: block;
    width: 100%;
    padding: 0;
    border: none;
    background: none;
    cursor: pointer;
    border-radius: 0.5rem;
    overflow: hidden;
  }

  .review-image-btn:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
  }

  .review-image {
    width: 100%;
    height: 12rem;
    object-fit: cover;
    border-radius: 0.5rem;
    display: block;
  }

  /* ── Author ─────────────────────────────────── */

  .review-author {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-top: auto;
    padding-top: 0.5rem;
    border-top: 1px solid #f4f4f5;
  }

  .review-avatar {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 9999px;
    background: #e4e4e7;
    color: #52525b;
    font-size: 0.75rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .review-author-info {
    min-width: 0;
  }

  .review-author-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: #27272a;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    flex-wrap: wrap;
  }

  .review-verified {
    display: inline-flex;
    align-items: center;
    gap: 0.1875rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: #16a34a;
  }

  .review-verified-icon {
    width: 0.875rem;
    height: 0.875rem;
  }

  .review-author-meta {
    font-size: 0.8125rem;
    color: #a1a1aa;
  }

  /* ── Show more ──────────────────────────────── */

  .reviews-show-more {
    margin-top: 1.5rem;
    text-align: center;
  }

  .reviews-show-more-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #3f3f46;
    background: #fff;
    border: 1px solid #d4d4d8;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: background-color 0.15s, border-color 0.15s;
  }

  .reviews-show-more-btn:hover {
    background: #f4f4f5;
    border-color: #a1a1aa;
  }

  .reviews-show-more-btn:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
  }

  /* ── Lightbox ───────────────────────────────── */

  .lightbox-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    animation: lightbox-fade-in 0.2s ease-out;
  }

  .lightbox-content {
    position: relative;
    max-width: 56rem;
    max-height: 90vh;
    width: 100%;
  }

  .lightbox-close {
    position: absolute;
    top: -2.5rem;
    right: 0;
    width: 2rem;
    height: 2rem;
    padding: 0;
    border: none;
    background: none;
    color: #fff;
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.15s;
  }

  .lightbox-close:hover {
    opacity: 1;
  }

  .lightbox-close svg {
    width: 100%;
    height: 100%;
  }

  .lightbox-image {
    width: 100%;
    max-height: 85vh;
    object-fit: contain;
    border-radius: 0.5rem;
    display: block;
  }

  @keyframes lightbox-fade-in {
    from {
      opacity: 0;
    }
    to {
      opacity: 1;
    }
  }
</style>
