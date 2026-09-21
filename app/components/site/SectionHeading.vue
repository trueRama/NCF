<script setup lang="ts">
defineProps<{ eyebrow?: string; title: string; lede?: string; align?: 'left' | 'center' }>()
</script>

<template>
  <div :class="align === 'center' ? 'mx-auto max-w-3xl text-center' : 'max-w-3xl'">
    <p v-if="eyebrow" class="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">
      {{ eyebrow }}
    </p>
    <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl">{{ title }}</h2>
    <p v-if="lede" class="mt-5 text-base leading-relaxed text-muted-foreground sm:text-lg">{{ lede }}</p>
  </div>
</template>
