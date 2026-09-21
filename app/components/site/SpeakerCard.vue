<script setup lang="ts">
import type { Speaker } from '@/data/forum'

const props = defineProps<{ speaker: Speaker }>()

const initials = computed(() =>
  props.speaker.name
    .replace(/^(Hon\.|Dr|Mr|Ms|Mrs|Prof\.)\s+/i, '')
    .split(/\s+/)
    .slice(0, 2)
    .map(w => w[0])
    .join('')
    .toUpperCase(),
)
</script>

<template>
  <article class="flex h-full items-start gap-3 border bg-card transition-colors hover:border-primary/40">
    <img
      v-if="speaker.photo"
      :src="speaker.photo"
      :alt="speaker.name"
      class="h-full w-32 shrink-0 object-cover"
    >
    <span
      v-else
      class="flex w-44 h-full shrink-0 items-center justify-center bg-primary/10 text-xl font-bold text-primary-ink"
      aria-hidden="true"
    >{{ initials }}</span>

    <div class="flex min-w-0 flex-col py-3">
      <span class="text-base font-bold leading-snug text-balance">{{ speaker.name }}</span>
      <p class="mt-1 text-sm leading-snug text-muted-foreground">{{ speaker.role }}</p>
      <p class="mt-1 text-sm font-semibold text-primary">{{ speaker.org }}</p>

      <p class="mt-2 text-xs leading-relaxed text-muted-foreground">{{ speaker.billing }}</p>

      <Badge
        v-if="speaker.provisional"
        variant="outline"
        class="mt-2 w-fit border-dashed text-xs font-normal"
      >
        To be confirmed
      </Badge>
    </div>
  </article>
</template>
