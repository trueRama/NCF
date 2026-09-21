<script setup lang="ts">
import { ArrowUpRight, CalendarDays, MapPin } from 'lucide-vue-next'
import { EVENT } from '@/data/forum'

const year = new Date().getFullYear()

const SECTIONS = [
  { id: 'about', label: 'About the Forum' },
  { id: 'agenda', label: 'Agenda' },
  { id: 'speakers', label: 'Speakers' },
  { id: 'resources', label: 'Resources' },
]

const go = (id: string) =>
  document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
</script>

<template>
  <footer class="mt-20 border-t bg-muted/30">
    <div class="mx-auto grid w-full max-w-7xl gap-10 px-4 py-14 md:grid-cols-[1.4fr_1fr_1fr]">
      <div>
        <div class="flex items-center gap-3">
          <img src="/img/brand/coat-of-arms.png" alt="" class="h-11 w-auto">
          <img src="/img/brand/mofped.png" alt="" class="h-10 w-auto">
        </div>
        <p class="mt-5 max-w-sm text-sm leading-relaxed text-muted-foreground">
          The National Competitiveness Forum is convened by the
          {{ EVENT.convenedBy }} of the {{ EVENT.ministry }}, under the
          {{ EVENT.directorate }}.
        </p>
        <dl class="mt-5 space-y-2 text-sm">
          <div class="flex items-center gap-2">
            <dt class="sr-only">Date</dt>
            <CalendarDays class="size-4 shrink-0 text-primary-ink" :stroke-width="1.75" />
            <dd>{{ EVENT.dateLabel }} · {{ EVENT.timeLabel }}</dd>
          </div>
          <div class="flex items-center gap-2">
            <dt class="sr-only">Venue</dt>
            <MapPin class="size-4 shrink-0 text-primary-ink" :stroke-width="1.75" />
            <dd>{{ EVENT.venue }}, {{ EVENT.city }}</dd>
          </div>
        </dl>
      </div>

      <nav aria-labelledby="footer-forum">
        <h2 id="footer-forum" class="text-xs font-semibold uppercase tracking-[0.14em] text-muted-foreground">
          This Forum
        </h2>
        <ul class="mt-4 space-y-2.5 text-sm">
          <li v-for="item in SECTIONS" :key="item.id">
            <button type="button" class="hover:text-primary-ink" @click="go(item.id)">
              {{ item.label }}
            </button>
          </li>
        </ul>
      </nav>

      <nav aria-labelledby="footer-portal">
        <h2 id="footer-portal" class="text-xs font-semibold uppercase tracking-[0.14em] text-muted-foreground">
          Doing Business Portal
        </h2>
        <ul class="mt-4 space-y-2.5 text-sm">
          <li>
            <a :href="EVENT.portalUrl" target="_blank" rel="noopener" class="inline-flex items-center gap-1 hover:text-primary-ink">
              Portal home <ArrowUpRight class="size-3.5" />
            </a>
          </li>
          <li>
            <a :href="`${EVENT.portalUrl}/reforms`" target="_blank" rel="noopener" class="inline-flex items-center gap-1 hover:text-primary-ink">
              Reforms Tracker <ArrowUpRight class="size-3.5" />
            </a>
          </li>
          <li>
            <a :href="`${EVENT.portalUrl}/events`" target="_blank" rel="noopener" class="inline-flex items-center gap-1 hover:text-primary-ink">
              Forum archive <ArrowUpRight class="size-3.5" />
            </a>
          </li>
          <li>
            <a :href="`${EVENT.portalUrl}/issues`" target="_blank" rel="noopener" class="inline-flex items-center gap-1 hover:text-primary-ink">
              Raise an issue <ArrowUpRight class="size-3.5" />
            </a>
          </li>
        </ul>
      </nav>
    </div>

    <div class="border-t">
      <div class="mx-auto flex w-full max-w-7xl px-4 py-6 text-xs text-muted-foreground">
        <p>© {{ year }} {{ EVENT.ministry }}, Republic of Uganda.</p>
      </div>
    </div>
  </footer>
</template>
