# 17th National Competitiveness Forum — event microsite

A standalone microsite for the **17th National Competitiveness Forum**,
*“From Funds to Firms: Turning GoU Wealth Creation Initiatives into Competitive
Enterprises for Uganda’s US$500 Billion Economy”* — Kampala Serena Hotel,
25 September 2026.

Convened by the Private Sector Development Unit, Ministry of Finance, Planning
and Economic Development.

## Stack

Built from the `nuxt-shad-pb-boilerplate` starter (git history removed), wearing
the Doing Business Portal theme.

- **Nuxt 4** (Vue 3), file-based routing
- **Client-rendered only** — `ssr: false`; `nuxt generate` emits a static bundle
- **Tailwind CSS v4** with the Portal's oklch design tokens (`app/assets/css/tailwind.css`)
- **shadcn-vue** (new-york), lucide icons, vue-sonner

## Shape of the project

Two halves, one deployment, one domain:

| Path | What it is |
| --- | --- |
| `app/`, `public/` | The **landing page** — Nuxt 4, client-rendered, built to a static bundle |
| `repository/` | The **file repository** — PHP 8 + MySQL, with its own admin portal |

The static bundle is published at the web root; the PHP app sits beneath it at
`/repository`. Because both are served from the same origin, the landing page
links straight into the repository with plain relative URLs — no CORS, no API.

## Commands

```bash
npm install
npm run dev        # landing page  → http://localhost:3000
npm run generate   # static bundle → .output/public
```

The PHP half needs Apache + MySQL (XAMPP locally):

```bash
# once, to create the database and apply the schema
php repository/setup.php

# after pulling a schema change into an existing install
# open http://localhost/ncf17/repository/migrate.php
```

To preview the two halves exactly as they deploy, build and serve them together:

```bash
npm run generate
mkdir preview && cp -r .output/public/. preview/ && cp -r repository preview/repository
php -S 127.0.0.1:8082 -t preview
```

## Structure

A **single page** with five anchored sections, mirroring the shape of the ABCD
portal launch page it replaces but wearing the Portal's own theme:

| Section | Contents |
| --- | --- |
| `#home` | Hero over a Forum photograph, live countdown, date/venue/convener, Access Portal CTA |
| — | Headline numbers behind the theme (PDM investment, parishes, households, exports) |
| `#about` | Why the Forum is about turning funds into firms, plus the five objectives |
| `#agenda` | Annex 1 as a Time / Activity / Session chair table |
| `#speakers` | All twelve speakers and session chairs |
| `#resources` | The three repositories — Event Resources, Reports, Commodity Profiles |

The header nav scroll-spies the section in view; `/anything-else` renders a 404
that points back to the page.

## Content

Every fact on the site lives in **`app/data/forum.ts`** — the event, the
programme, speakers, past editions, resources. Nothing is fetched at runtime.

The module also holds sourced content the one-pager does not currently render
(`BACKGROUND`, `CHALLENGES`, `SOLUTION`, `EXPECTED_OUTCOMES`, `ARCHIVE`,
`LAST_FORUM_PRIORITIES`, `PARTICIPATING`, `GALLERY`). It is kept for when a
section needs it rather than being re-extracted from the concept note.

Sources:

- The **draft** 17th NCF concept note of 22 July 2026 (theme, background,
  justification, objectives, Annex 1 programme, Annex II archive).
- The Doing Business Portal's own forum archive and the 16th NCF outcome matrix
  (the nine priorities, the 18 tracked actions, the Forum photography).

### Placeholder data

The concept note names some presenters only by office. Those entries carry
`provisional: true` in `SPEAKERS` and render with a **“To be confirmed”** badge
rather than passing as settled:

| Placeholder | What the concept note actually says |
| --- | --- |
| Hon. Matia Kasaija | “Key note address by Hon. MFPED” |
| Hon. Amos Lugolobi | “Closing Remarks by MSFPED – Planning” |
| Ms Dianna Nanono | “Welcoming Remarks by H/PSDU” |
| Ms Sarah Nabbanja | “The presenter – DCO/Mubende District” |
| Mr David Ssemwanga | “Commissioner, Local Economic Growth Support, MoLG” |
| Mr Anil Sharma | “Best practices from India” |
| Ms Grace Muhumuza | “Presenter: Stanbic Bank” |

Replace the names in `app/data/forum.ts` and drop `provisional: true` as PSDU
confirms each one.

## The three repositories

`#resources` on the landing page shows three cards. Each one is a repository of
files held by the PHP app and administered from `/repository/admin/`:

| Card | `files.category` | Download | View |
| --- | --- | --- | --- |
| Event Resources | `event_resources` | `/repository/download_all.php?category=event_resources` | `/repository/client/?category=event_resources` |
| Reports | `reports` | `…?category=reports` | `…?category=reports` |
| Commodity Profiles | `commodity_profiles` | `…?category=commodity_profiles` | `…?category=commodity_profiles` |

**Download** streams every file in that repository as a single zip, built per
request so it always reflects the latest uploads. **View** opens the browsable
listing, where files can be taken one at a time.

The card definitions live in `RESOURCES` in `app/data/forum.ts`; the `category`
values there must match the `files.category` enum, which is declared once in
`repository/includes/schema.php` and used by both the schema and the UI.

### Database

`files` carries two independent axes:

- `event_id` — which Forum the file belongs to (the existing multi-event/QR system)
- `category` — which of the three repositories it appears in

Linking without `?event=` shows a category across every event, which is what the
landing page cards do. Adding `&event=<code>` scopes it to one Forum, which is
what the QR codes do. `repository/migrate.php` adds `category` to an existing
install and defaults every current row to `event_resources`.

## Configuration

Credentials never live in source. `repository/includes/config.php` reads them
from `repository/includes/config.local.php` (gitignored) or from environment
variables.

**The environment is detected automatically.** `localhost`, `127.0.0.1`,
`192.168.*`, `*.test`, `*.local` and the CLI are treated as `local`; any other
hostname is `production`. An unknown host falls to `production` deliberately —
it fails closed rather than exposing errors on a live site. Set `APP_ENV` to
decide explicitly.

So one config file can serve both:

```php
// repository/includes/config.local.php
return [
    'shared'     => ['admin_username' => 'admin', 'admin_password_hash' => '...'],
    'local'      => ['db_name' => 'ncf_repository', 'db_user' => 'root', 'db_pass' => ''],
    'production' => ['db_name' => '...', 'db_user' => '...', 'db_pass' => '...'],
];
```

A flat array (no environment keys) still works and applies everywhere.
Environment variables — `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`,
`ADMIN_USERNAME`, `ADMIN_PASSWORD_HASH`, `LANDING_PAGE_URL` — override the file.

Start from the example:

```bash
cp repository/includes/config.local.example.php repository/includes/config.local.php
```

Set `admin_password_hash` before going live — until you do, the admin portal
still accepts the default `admin`/`admin` and shows a warning banner:

```bash
php -r "echo password_hash('your-password', PASSWORD_DEFAULT);"
```

In `production` the app also stops displaying PHP errors and stops
auto-creating the database; a connection failure returns a plain message and
logs the detail instead.

## Deployment

One package, one upload: the static bundle at the web root, `repository/`
beneath it.

```bash
npm run package      # generate + assemble + validate -> deploy/
```

`scripts/package.mjs` is the single source of truth — CI runs the same script,
so a local package and a deployed one are identical. It refuses to build
without complete credentials, rejects an `ADMIN_PASSWORD_HASH` that is not
bcrypt, and checks the landing page really links into the repository.

`.github/workflows/deploy-ftp.yml` runs it on push to `main`/`master`, uploads
`deploy/` over FTPS, then health-checks the live URLs. Run it manually with the
**dry_run** input to build and validate without uploading.

| Secret | |
| --- | --- |
| `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD` | FTP account |
| `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` | production database |
| `ADMIN_PASSWORD_HASH` | bcrypt hash for the admin portal |

Optional secrets: `FTP_SERVER_DIR` (default `public_html/`), `FTP_PROTOCOL`
(default `ftps`), `FTP_PORT`, `ADMIN_USERNAME`,
`NUXT_PUBLIC_REGISTRATION_ENDPOINT`.

Optional repository **variables**: `SITE_URL` (enables the post-deploy health
check — set it to `https://ncf.miichub.com`) and `LANDING_PAGE_URL`.

The deployed layout puts the Forum landing page at `https://ncf.miichub.com/`
and the repository beneath it at `https://ncf.miichub.com/repository/`. Because
they share an origin, the repository's logo links home with a plain relative
hop and needs no configuration in production.

Delegate uploads live only on the server: `repository/uploads/**` is excluded
from the sync, so a deploy never deletes them.

After a deploy that changes the schema, open `/repository/migrate.php` once.
