<script setup lang="ts">
import { ArrowUpRight, ArrowDown, FileText, Eye, Download } from 'lucide-vue-next'
import { EVENT, HEADLINE_STATS, INTRO, OBJECTIVES, SPEAKERS, RESOURCES } from '@/data/forum'
import type { ResourceCategory } from '@/data/forum'

const go = (id: string) =>
  document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' })

/**
 * No collection service has been chosen for registration yet. With
 * `registrationEndpoint` unset — the shipped default — Register falls back to a
 * pre-filled email to the Private Sector Development Unit rather than dropping
 * a delegate's details. Set NUXT_PUBLIC_REGISTRATION_ENDPOINT to switch it over.
 */
const { public: cfg } = useRuntimeConfig()

const registerHref = computed(() => {
  if (cfg.registrationEndpoint) return cfg.registrationEndpoint
  const subject = `Registration — ${EVENT.title}`
  const body = [
    `I would like to register for the ${EVENT.title} on ${EVENT.dateLabel} at ${EVENT.venue}.`,
    '',
    'Name:',
    'Organisation:',
    'Job title:',
    'Email:',
    'Telephone:',
  ].join('\r\n')
  return `mailto:${cfg.registrationEmail}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`
})

/**
 * The three Resources cards hand off to the PHP file repository: Download
 * streams the whole category as a zip, View opens the browsable listing where
 * files can be taken one at a time.
 */
const repositoryBase = computed(() => cfg.repositoryBase.replace(/\/$/, ''))

const downloadAllHref = (category: ResourceCategory) =>
  `${repositoryBase.value}/download_all.php?category=${category}`

const viewRepoHref = (category: ResourceCategory) =>
  `${repositoryBase.value}/client/?category=${category}`
</script>

<template>
  <div>
    <!-- Home ------------------------------------------------------------- -->
    <section id="home" class="relative isolate overflow-hidden bg-neutral-950">
      <img
        src="/img/forum/ncf16-delegates-group.jpg"
        alt=""
        class="absolute inset-0 size-full object-cover opacity-70"
      >
      <div
        class="absolute inset-0 bg-gradient-to-b from-neutral-950/85 via-neutral-950/70 to-neutral-950/85"
        aria-hidden="true"
      />

      <div class="relative mx-auto flex w-full max-w-4xl flex-col items-center px-4 py-16 text-center sm:py-24 lg:py-28">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-white/60">
          Welcome to the
        </p>
        <h1 class="mt-4 text-[2rem] font-semibold leading-[1.12] tracking-tight text-primary sm:text-5xl lg:text-[3.5rem]">
          {{ EVENT.title }}
        </h1>

        <div class="mt-9 max-w-3xl">
          <h2 class="text-xs font-semibold uppercase tracking-[0.18em] text-white/60">
            Theme
          </h2>
          <p class="mt-3 text-lg leading-relaxed text-white/85 sm:text-xl">
            {{ EVENT.theme }}
          </p>
        </div>

        <!-- Save the date: countdown and the date itself in one card -------- -->
        <div class="mt-12 w-full max-w-3xl border border-white/20 bg-white/[0.07] px-5 py-8 backdrop-blur-sm sm:px-10 sm:py-10">
          <SiteCountdown tone="dark" label="Save the date" />

          <dl class="mt-8 border-t border-white/15 pt-6 text-white/90">
            <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-white/50">Date</dt>
            <dd class="mt-2 text-lg font-bold sm:text-xl">
              {{ EVENT.dateLabel }} · {{ EVENT.timeLabel }}
            </dd>
            <dd class="mt-1 text-sm text-white/70">
              {{ EVENT.venue }}, {{ EVENT.city }}
            </dd>
          </dl>
        </div>

        <div class="mt-10 flex flex-wrap justify-center gap-3">
          <Button as-child size="lg">
            <a :href="registerHref">
              Register <ArrowUpRight class="size-4" />
            </a>
          </Button>
          <Button as-child size="lg" variant="outline" class="border-white/25 bg-transparent text-white hover:bg-white/10 hover:text-white">
            <a :href="EVENT.portalUrl" target="_blank" rel="noopener">
              Access Portal <ArrowUpRight class="size-4" />
            </a>
          </Button>
        </div>

        <button
          type="button"
          class="mt-10 inline-flex items-center gap-1.5 text-sm font-medium text-white/70 hover:text-white"
          @click="go('agenda')"
        >
          See the agenda <ArrowDown class="size-4" />
        </button>
      </div>
    </section>

    <!-- Headline numbers -------------------------------------------------- -->
    <section class="border-b bg-card" aria-label="The numbers behind the theme">
      <dl class="mx-auto grid w-full max-w-7xl grid-cols-2 divide-x divide-y border-x lg:grid-cols-4 lg:divide-y-0">
        <div v-for="stat in HEADLINE_STATS" :key="stat.label" class="px-5 py-9 text-center sm:px-6">
          <h5 class="text-3xl text-primary tracking-tight tabular-nums sm:text-4xl">{{ stat.value }}</h5>
          <dt class="mt-2.5 text-sm font-medium leading-snug">{{ stat.label }}</dt>
          <p class="mt-1.5 text-xs uppercase tracking-[0.1em] text-muted-foreground">{{ stat.note }}</p>
        </div>
      </dl>
    </section>

    <!-- About Event -------------------------------------------------------- -->
    <section id="about" class="scroll-mt-24 border-b">
      <div class="mx-auto w-full max-w-7xl px-4 py-16 sm:py-20">
        <SiteSectionHeading
          align="center"
          title="About Event"
        />

        <div class="mx-auto mt-10 max-w-3xl space-y-5 text-center text-base leading-relaxed text-muted-foreground sm:text-lg">
          <p v-for="(para, i) in INTRO" :key="i">{{ para }}</p>
        </div>

        <div class="mx-auto mt-14 max-w-4xl border bg-card p-6 sm:p-8">
          <h3 class="text-center text-xl font-bold tracking-tight sm:text-2xl">
            Objectives
          </h3>
          <ol class="mt-6 divide-y">
            <li v-for="(objective, i) in OBJECTIVES" :key="i" class="flex gap-4 py-4 first:pt-0 last:pb-0">
              <span class="text-sm font-bold tabular-nums text-primary-ink">
                {{ String(i + 1).padStart(2, '0') }}
              </span>
              <p class="text-base leading-relaxed">{{ objective }}</p>
            </li>
          </ol>
        </div>
      </div>
    </section>

    <!-- Agenda -------------------------------------------------------------- -->
    <section id="agenda" class="scroll-mt-24 border-b bg-muted/30">
      <div class="mx-auto w-full max-w-7xl px-4 py-16 sm:py-20">
        <SiteSectionHeading
          align="center"
          title="Event Agenda"
         
        />


        <SiteAgenda class="mt-8" />

      </div>
    </section>

    <!-- Speakers ------------------------------------------------------------- -->
    <section id="speakers" class="scroll-mt-24 border-b">
      <div class="mx-auto w-full max-w-7xl px-4 py-16 sm:py-20">
        <SiteSectionHeading
          align="center"
          title="Event Speakers"
        />

        <!--
          Two rows that scroll sideways, per the review sketch. `grid-flow-col`
          fills down then across, so the reading order stays column-by-column
          and the row count is fixed rather than reflowing with the viewport.
        -->
        <div class="-mx-4 mt-10 overflow-x-auto px-4 pb-4">
          <ul class="grid auto-cols-[min(85vw,25rem)] grid-flow-col grid-rows-2 gap-5">
            <li v-for="speaker in SPEAKERS" :key="speaker.name" class="h-full">
              <SiteSpeakerCard :speaker="speaker" />
            </li>
          </ul>
        </div>

        <p class="mt-3 text-xs text-muted-foreground">Scroll sideways for more speakers.</p>
      </div>
    </section>

    <!-- Resources -------------------------------------------------------------- -->
    <section id="resources" class="scroll-mt-24 bg-muted/30">
      <div class="mx-auto w-full max-w-7xl px-4 py-16 sm:py-20">
        <SiteSectionHeading
          align="center"
          title="Event Resources"
        />

        <ul class="mt-9 grid border-l border-t md:grid-cols-2 lg:grid-cols-3">
          <li v-for="resource in RESOURCES" :key="resource.category" class="flex flex-col border-b border-r bg-card gap-3 p-3">
            <div class="flex items-center justify-between gap-3">
              <span class="mt-3 text-lg font-semibold leading-snug">{{ resource.title }}</span>
              <FileText
                class="size-4 shrink-0 text-muted-foreground"
                :stroke-width="1.75"
                aria-hidden="true"
              />
            </div>

            <p class="mt-2.5 flex-1 text-sm leading-relaxed text-muted-foreground">
              {{ resource.description }}
            </p>

            <div class="grid grid-cols-2 gap-2 items-center w-full">
              <Button as-child class="w-full">
                <a :href="downloadAllHref(resource.category)">
                  Download <Download />
                  <span class="sr-only">all {{ resource.title }} files as a zip archive</span>
                </a>
              </Button>
              <Button as-child class="w-full" variant="outline">
                <a :href="viewRepoHref(resource.category)">
                  View <Eye />
                  <span class="sr-only">the {{ resource.title }} repository</span>
                </a>
              </Button>
            </div>
          </li>
        </ul>
      </div>
    </section>
  </div>
</template>
