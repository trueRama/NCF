<script setup lang="ts">
useHead({ title: 'Page not found — 17th National Competitiveness Forum' })
</script>

<template>
  <div class="mx-auto flex w-full max-w-3xl flex-col items-start px-4 py-24 sm:py-32">
    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-primary-ink">404</p>
    <h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">
      That page is not part of this Forum
    </h1>
    <p class="mt-4 text-base leading-relaxed text-muted-foreground">
      This site is a single page. The agenda, speakers and resources are all on it.
    </p>
    <div class="mt-8 flex flex-wrap gap-3">
      <Button as-child>
        <NuxtLink to="/">Back to the Forum</NuxtLink>
      </Button>
    </div>
  </div>
</template>
