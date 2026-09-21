// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  // Client-rendered only. The site is a standalone microsite for a single
  // event: there is no per-request data, so it ships as a static SPA bundle
  // that Render serves from a CDN with no Node process to keep warm.
  ssr: false,

  nitro: {
    preset: 'static',
    prerender: {
      // Emit the SPA shell as 200.html/404.html so Render's rewrite can hand
      // any deep link (/programme, /speakers, …) back to the same bundle.
      failOnError: false,
    },
  },

  app: {
    head: {
      htmlAttrs: { lang: 'en' },
      title: '17th National Competitiveness Forum — 25 September 2026',
      meta: [
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        {
          name: 'description',
          content:
            'From Funds to Firms: turning Government wealth creation initiatives into competitive enterprises for Uganda’s US$500 billion economy. Kampala Serena Hotel, 25 September 2026.',
        },
        { property: 'og:title', content: '17th National Competitiveness Forum' },
        {
          property: 'og:description',
          content:
            'From Funds to Firms — Kampala Serena Hotel, 25 September 2026. Convened by the Private Sector Development Unit, Ministry of Finance, Planning and Economic Development.',
        },
        { property: 'og:type', content: 'website' },
      ],
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
        { rel: 'icon', type: 'image/png', sizes: '32x32', href: '/favicon-32x32.png' },
        { rel: 'icon', type: 'image/png', sizes: '16x16', href: '/favicon-16x16.png' },
        { rel: 'apple-touch-icon', sizes: '180x180', href: '/apple-touch-icon.png' },
      ],
    },
    pageTransition: { name: 'page', mode: 'out-in' },
  },

  runtimeConfig: {
    public: {
      // Where the registration form posts. Empty by default: with nothing set
      // the form falls back to a pre-filled email rather than silently dropping
      // a delegate's details. Set NUXT_PUBLIC_REGISTRATION_ENDPOINT once a
      // collection service (a PocketBase collection, a form service) is chosen.
      registrationEndpoint: '',
      // Fallback address for registrations and enquiries.
      registrationEmail: 'psdu@finance.go.ug',
      // The PHP file repository, deployed alongside this bundle. Same-origin by
      // default; set NUXT_PUBLIC_REPOSITORY_BASE to an absolute URL if the two
      // halves ever end up on separate hosts.
      repositoryBase: '/repository',
    },
  },

  modules: ['@nuxtjs/tailwindcss', 'shadcn-nuxt'],

  shadcn: {
    prefix: '',
    componentDir: '@/components/ui',
  },
})
