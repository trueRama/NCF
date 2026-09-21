<script setup lang="ts">
import { EVENT } from '@/data/forum'

const props = withDefaults(
  defineProps<{ tone?: 'light' | 'dark'; label?: string }>(),
  { tone: 'dark', label: 'Counting down to the Forum' },
)

const { parts, elapsed } = useCountdown(EVENT.startsAt)

const cells = computed(() => [
  { label: 'Days', value: parts.value.days },
  { label: 'Hours', value: parts.value.hours },
  { label: 'Minutes', value: parts.value.minutes },
  { label: 'Seconds', value: parts.value.seconds },
])

const pad = (n: number) => String(n).padStart(2, '0')
</script>

<template>
  <div class="text-center">
    <p
      class="text-sm font-bold uppercase tracking-[0.18em]"
      :class="props.tone === 'dark' ? 'text-white' : 'text-foreground'"
    >
      {{ elapsed ? 'The Forum is under way' : props.label }}
    </p>

    <dl class="mt-6 flex justify-center gap-2.5 sm:gap-4">
      <div
        v-for="cell in cells"
        :key="cell.label"
        class="flex min-w-[4.5rem] flex-col items-center border px-2 py-4 sm:min-w-[7rem] sm:px-4 sm:py-5"
        :class="props.tone === 'dark' ? 'border-white/20 bg-white/[0.08]' : 'border-border bg-card'"
      >
        <span
          class="text-3xl font-bold leading-none tracking-tight tabular-nums sm:text-5xl lg:text-6xl"
          :class="props.tone === 'dark' ? 'text-white' : 'text-foreground'"
        >
          {{ pad(cell.value) }}
        </span>
        <dt
          class="mt-3 text-[0.6rem] font-semibold uppercase tracking-[0.14em] sm:text-xs"
          :class="props.tone === 'dark' ? 'text-white/60' : 'text-muted-foreground'"
        >
          {{ cell.label }}
        </dt>
      </div>
    </dl>
  </div>
</template>
