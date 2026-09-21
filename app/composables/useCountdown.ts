/**
 * Ticking countdown to an ISO instant. Client-only by construction — the site
 * is rendered in the browser, so there is no server/client clock to reconcile.
 */
export function useCountdown(target: string) {
  const remaining = ref(Math.max(0, new Date(target).getTime() - Date.now()))
  let timer: ReturnType<typeof setInterval> | undefined

  const tick = () => {
    remaining.value = Math.max(0, new Date(target).getTime() - Date.now())
  }

  onMounted(() => {
    tick()
    timer = setInterval(tick, 1000)
  })
  onBeforeUnmount(() => timer && clearInterval(timer))

  const parts = computed(() => {
    const total = Math.floor(remaining.value / 1000)
    return {
      days: Math.floor(total / 86400),
      hours: Math.floor((total % 86400) / 3600),
      minutes: Math.floor((total % 3600) / 60),
      seconds: total % 60,
    }
  })

  const elapsed = computed(() => remaining.value === 0)

  return { parts, elapsed }
}
