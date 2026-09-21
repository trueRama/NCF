<script setup lang="ts">
import { Info } from 'lucide-vue-next'
</script>

<template>
  <div class="flex items-start gap-3 border border-dashed border-primary/40 bg-primary/5 px-4 py-3.5">
    <Info class="mt-0.5 size-4 shrink-0 text-primary-ink" :stroke-width="2" />
    <p class="text-sm leading-relaxed text-muted-foreground">
      <slot>
        Details on this page come from the <strong class="font-medium text-foreground">draft</strong>
        concept note of 22 July 2026. Sessions, presenters and timings are subject to
        confirmation by the Private Sector Development Unit.
      </slot>
    </p>
  </div>
</template>
