<script setup lang="ts">
import { Menu, ArrowUpRight } from 'lucide-vue-next'
import { EVENT } from '@/data/forum'

/** One page, so the nav is a set of in-page anchors. */
const NAV = [
  { id: 'home', label: 'Home' },
  { id: 'about', label: 'About Event' },
  { id: 'agenda', label: 'Agenda' },
  { id: 'speakers', label: 'Speakers' },
  { id: 'resources', label: 'Resources' },
]

const open = ref(false)

// Scroll progress, a condensed bar, and which section is in view.
const progress = ref(0)
const condensed = ref(false)
const active = ref('home')

const onScroll = () => {
  const h = document.documentElement.scrollHeight - window.innerHeight
  progress.value = h > 0 ? (window.scrollY / h) * 100 : 0
  condensed.value = window.scrollY > 24

  // The section whose top has most recently passed under the header.
  const line = window.scrollY + 120
  let current = 'home'
  for (const item of NAV) {
    const el = document.getElementById(item.id)
    if (el && el.offsetTop <= line) current = item.id
  }
  // At the very bottom the last section may never reach the line.
  if (window.innerHeight + window.scrollY >= document.body.scrollHeight - 2) {
    current = NAV[NAV.length - 1]!.id
  }
  active.value = current
}

onMounted(() => {
  onScroll()
  window.addEventListener('scroll', onScroll, { passive: true })
})
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll))

const go = (id: string) => {
  open.value = false
  document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}
</script>

<template>
  <header
    class="sticky top-0 z-50 border-b bg-background/85 backdrop-blur-md transition-shadow"
    :class="condensed && 'shadow-sm'"
  >
    <div
      class="absolute inset-x-0 top-0 h-0.5 origin-left bg-primary transition-transform duration-150"
      :style="{ transform: `scaleX(${progress / 100})` }"
      aria-hidden="true"
    />

    <div
      class="mx-auto flex w-full max-w-7xl items-center gap-4 px-4 transition-all"
      :class="condensed ? 'h-14' : 'h-16 sm:h-20'"
    >
      <button
        type="button"
        class="flex items-center gap-3 text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
        @click="go('home')"
      >
        <img
          src="/img/brand/mofped.png"
          alt=""
          class="w-auto shrink-0 transition-all"
          :class="condensed ? 'h-9' : 'h-10 sm:h-12'"
        >
        <span class="flex flex-col leading-tight">
          <span class="text-[0.65rem] font-semibold uppercase tracking-[0.14em] text-muted-foreground">
            {{ EVENT.edition }} National
          </span>
          <span class="text-sm font-semibold tracking-tight sm:text-base">Competitiveness Forum</span>
        </span>
      </button>

      <nav class="ml-auto hidden items-center gap-1 lg:flex" aria-label="Sections">
        <button
          v-for="item in NAV"
          :key="item.id"
          type="button"
          class="relative px-3 py-2 text-sm font-medium transition-colors"
          :class="active === item.id ? 'text-foreground' : 'text-muted-foreground hover:text-foreground'"
          :aria-current="active === item.id ? 'true' : undefined"
          @click="go(item.id)"
        >
          {{ item.label }}
          <span
            v-if="active === item.id"
            class="absolute inset-x-3 -bottom-px h-0.5 bg-primary"
            aria-hidden="true"
          />
        </button>
      </nav>

      <div class="ml-auto flex items-center gap-2 lg:ml-0">
        <Button as-child size="sm" class="hidden sm:inline-flex">
          <a :href="EVENT.portalUrl" target="_blank" rel="noopener">Access Portal</a>
        </Button>

        <Sheet v-model:open="open">
          <SheetTrigger as-child>
            <Button variant="outline" size="icon" class="lg:hidden" aria-label="Open menu">
              <Menu class="size-4" />
            </Button>
          </SheetTrigger>
          <SheetContent side="right" class="w-[86vw] max-w-sm">
            <SheetHeader>
              <SheetTitle class="text-left">{{ EVENT.edition }} National Competitiveness Forum</SheetTitle>
              <SheetDescription class="text-left">
                {{ EVENT.dateLabel }} · {{ EVENT.venue }}
              </SheetDescription>
            </SheetHeader>
            <nav class="mt-2 flex flex-col px-4" aria-label="Sections">
              <button
                v-for="item in NAV"
                :key="item.id"
                type="button"
                class="border-b py-3.5 text-left text-base font-medium transition-colors"
                :class="active === item.id ? 'text-foreground' : 'text-muted-foreground hover:text-foreground'"
                @click="go(item.id)"
              >
                {{ item.label }}
              </button>
              <Button as-child class="mt-6">
                <a :href="EVENT.portalUrl" target="_blank" rel="noopener">
                  Access Portal <ArrowUpRight class="size-4" />
                </a>
              </Button>
            </nav>
          </SheetContent>
        </Sheet>
      </div>
    </div>
  </header>
</template>
