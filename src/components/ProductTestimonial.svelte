<script>
  let { quote, author, stars = '★★★★★' } = $props();
</script>

<blockquote class="brand-product-review">
  <p>{quote}</p>
  <cite>{author} {stars}</cite>
</blockquote>

<style>
  .brand-product-review {
    @apply mt-6 border-t border-zinc-300 pt-5;
  }

  .brand-product-review p {
    @apply text-xl leading-relaxed text-zinc-700;
  }

  .brand-product-review cite {
    @apply mt-2 block text-lg text-zinc-500;
    font-style: normal;
  }
</style>
