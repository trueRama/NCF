<script setup lang="ts">
import { PROGRAMME } from '@/data/forum'

/**
 * Two renderings of one running order.
 *
 * A three-column table does not survive a 390px viewport — it either scrolls
 * sideways or squeezes the activity column to a ribbon. So narrow screens get
 * the same content stacked, and the table appears from `md` up.
 */
</script>

<template>
  <div>
    <!-- Narrow screens: stacked ---------------------------------------- -->
    <ol class="flex flex-col gap-4 md:hidden">
      <li
        v-for="block in PROGRAMME"
        :key="block.id"
        class="border p-4"
        :class="block.kind === 'session' ? 'bg-card' : 'bg-muted/40'"
      >
        <p class="text-xs font-semibold uppercase tracking-[0.12em] tabular-nums text-primary-ink">
          {{ block.time }}
        </p>
        <h3
          class="mt-2 text-base font-semibold leading-snug"
          :class="block.kind !== 'session' && 'text-muted-foreground'"
        >
          {{ block.title }}
        </h3>
        <p v-if="block.strapline" class="mt-1.5 text-sm leading-relaxed text-muted-foreground">
          {{ block.strapline }}
        </p>

        <ol v-if="block.items" class="mt-4 space-y-3">
          <li v-for="(item, i) in block.items" :key="i" class="flex gap-2.5">
            <span class="mt-2 size-1 shrink-0 bg-primary" aria-hidden="true" />
            <div class="min-w-0">
              <p class="text-sm leading-relaxed">
                {{ item.text }}
                <span v-if="item.label" class="ml-1 whitespace-nowrap text-xs tabular-nums text-muted-foreground">
                  ({{ item.label }})
                </span>
              </p>
              <ul v-if="item.children" class="mt-2 flex flex-wrap gap-1.5">
                <li
                  v-for="child in item.children"
                  :key="child"
                  class="border bg-background px-2 py-0.5 text-xs text-muted-foreground"
                >
                  {{ child }}
                </li>
              </ul>
            </div>
          </li>
        </ol>

        <div v-if="block.chair" class="mt-4 border-t pt-3">
          <p class="text-xs uppercase tracking-[0.12em] text-muted-foreground">Session chair</p>
          <p class="mt-1 text-sm font-medium">{{ block.chair.name }}</p>
          <p class="text-sm leading-snug text-muted-foreground">{{ block.chair.role }}</p>
        </div>
      </li>
    </ol>

    <!-- md and up: the table ------------------------------------------- -->
    <div class="hidden border bg-card md:block">
      <table class="w-full table-fixed text-left">
        <caption class="sr-only">Running order for the Forum</caption>
        <thead>
          <tr class="border-b bg-primary !text-white font-black">
            <th scope="col" class=" w-32 px-4 py-3.5 uppercase tracking-[0.12em] lg:w-40 lg:px-5">
              Time
            </th>
            <th scope="col" class=" px-4 py-3.5 uppercase tracking-[0.12em] lg:px-5">
              Activity
            </th>
            <th scope="col" class="w-48 px-4 py-3.5 uppercase tracking-[0.12em] lg:w-60 lg:px-5">
              Session chair
            </th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr
            v-for="block in PROGRAMME"
            :key="block.id"
            :class="block.kind !== 'session' && 'bg-muted/30'"
          >
            <td class="border-r px-4 py-5 align-top text-sm font-black tabular-nums text-primary lg:px-5">
              {{ block.time }}
            </td>

            <td class="border-r px-4 py-5 align-top lg:px-5">
              <p
                class="text-base font-semibold leading-snug"
                :class="block.kind !== 'session' && 'text-muted-foreground'"
              >
                {{ block.title }}
              </p>
              <p v-if="block.strapline" class="mt-1 text-sm leading-relaxed text-muted-foreground">
                <strong>Theme: </strong>{{ block.strapline }}
              </p>

              <ol v-if="block.items" class="mt-4 space-y-3">
                <li v-for="(item, i) in block.items" :key="i" class="flex gap-3">
                  <span class="mt-2 size-1 shrink-0 bg-primary" aria-hidden="true" />
                  <div class="min-w-0">
                    <p class="text-sm leading-relaxed">
                      {{ item.text }}
                      <span v-if="item.label" class="ml-1 whitespace-nowrap text-xs tabular-nums text-muted-foreground">
                        ({{ item.label }})
                      </span>
                    </p>
                    <ol v-if="item.children" class="mt-2 ml-5 gap-1.5 list-decimal flex flex-col">
                      <li
                        v-for="child in item.children"
                        :key="child"
                        class=" text-sm"
                      >
                        {{ child }}
                      </li>
                    </ol>
                  </div>
                </li>
              </ol>
            </td>

            <td class="px-4 py-5 align-top lg:px-5">
              <template v-if="block.chair">
                <p class="text-sm font-medium">{{ block.chair.name }}</p>
                <p class="mt-0.5 text-sm leading-snug text-muted-foreground">{{ block.chair.role }}</p>
              </template>
              <span v-else class="text-sm text-muted-foreground" aria-hidden="true">—</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
