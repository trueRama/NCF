// Assembles the deployable package in deploy/: the static landing page at the
// root, the PHP repository beneath it at /repository.
//
//   npm run package
//
// Production credentials come from the environment (DB_HOST, DB_NAME, DB_USER,
// DB_PASS, ADMIN_PASSWORD_HASH, optionally ADMIN_USERNAME, LANDING_PAGE_URL)
// and are written to repository/includes/config.local.php. Pass --skip-config
// to assemble without them.

import { cp, mkdir, rm, writeFile, readFile, readdir, stat } from 'node:fs/promises'
import { existsSync } from 'node:fs'
import path from 'node:path'
import process from 'node:process'

const root = path.resolve(import.meta.dirname, '..')
const buildDir = path.join(root, '.output', 'public')
const outDir = path.join(root, 'deploy')

const skipConfig = process.argv.includes('--skip-config')

/** Never shipped: local-only files, docs, and runtime data owned by the server. */
const EXCLUDED = new Set([
  'uploads',
  'config.local.php',
  'config.local.example.php',
  '.env.deployment',
  'deploy.sh',
  'deploy.bat',
  'setup.php',
])

const UPLOADS_HTACCESS = `# Prevent PHP execution in uploads folder
<Files *.php>
    Order Deny,Allow
    Deny from all
</Files>

# Allow only specific file types
<FilesMatch "\\.(pdf|jpg|jpeg|png|gif)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>
`

function fail(message) {
  console.error(`\n  ERROR  ${message}\n`)
  process.exit(1)
}

async function assemble() {
  if (!existsSync(buildDir)) {
    fail('.output/public is missing — run `npm run generate` first.')
  }

  await rm(outDir, { recursive: true, force: true })
  await mkdir(outDir, { recursive: true })

  await cp(buildDir, outDir, { recursive: true })

  await cp(path.join(root, 'repository'), path.join(outDir, 'repository'), {
    recursive: true,
    filter: (src) => {
      const name = path.basename(src)
      return !EXCLUDED.has(name) && !name.endsWith('.md')
    },
  })

  const uploads = path.join(outDir, 'repository', 'uploads')
  await mkdir(uploads, { recursive: true })
  await writeFile(path.join(uploads, '.htaccess'), UPLOADS_HTACCESS)
}

function phpValue(value) {
  return `'${String(value).replace(/\\/g, '\\\\').replace(/'/g, "\\'")}'`
}

async function writeProductionConfig() {
  const production = {
    db_host: process.env.DB_HOST ?? '',
    db_name: process.env.DB_NAME ?? '',
    db_user: process.env.DB_USER ?? '',
    db_pass: process.env.DB_PASS ?? '',
  }
  const shared = {
    admin_username: process.env.ADMIN_USERNAME || 'admin',
    admin_password_hash: process.env.ADMIN_PASSWORD_HASH ?? '',
    landing_page_url: process.env.LANDING_PAGE_URL || '',
  }

  const missing = Object.entries(production)
    .filter(([, value]) => value === '')
    .map(([key]) => key.toUpperCase())
  if (shared.admin_password_hash === '') missing.push('ADMIN_PASSWORD_HASH')
  if (missing.length) {
    fail(`Missing required environment variables: ${missing.join(', ')}`)
  }
  if (!shared.admin_password_hash.startsWith('$2y$')) {
    fail('ADMIN_PASSWORD_HASH does not look like a bcrypt hash.')
  }

  const block = (obj) =>
    Object.entries(obj)
      .map(([key, value]) => `        ${phpValue(key)} => ${phpValue(value)},`)
      .join('\n')

  const contents = `<?php
// Generated at deploy time. Do not edit on the server.
return array (
    'shared' => array (
${block(shared)}
    ),
    'production' => array (
${block(production)}
    ),
);
`
  await writeFile(path.join(outDir, 'repository', 'includes', 'config.local.php'), contents)
}

async function walk(dir) {
  const entries = await readdir(dir, { withFileTypes: true })
  const files = []
  for (const entry of entries) {
    const full = path.join(dir, entry.name)
    if (entry.isDirectory()) files.push(...(await walk(full)))
    else files.push(full)
  }
  return files
}

async function validate() {
  const required = [
    'index.html',
    'repository/index.php',
    'repository/client/index.php',
    'repository/admin/index.php',
    'repository/download_all.php',
    'repository/includes/config.php',
    'repository/includes/settings.php',
    'repository/uploads/.htaccess',
  ]
  if (!skipConfig) required.push('repository/includes/config.local.php')

  for (const rel of required) {
    if (!existsSync(path.join(outDir, rel))) fail(`Missing from package: ${rel}`)
  }

  // The example config carries no secrets but invites editing on the server.
  for (const forbidden of ['repository/includes/config.local.example.php', 'repository/setup.php']) {
    if (existsSync(path.join(outDir, forbidden))) fail(`Should not be packaged: ${forbidden}`)
  }

  const files = await walk(outDir)

  // The landing page must actually link into the repository. The URL is built
  // at runtime from repositoryBase, so check the pieces rather than a full path.
  const html = await Promise.all(
    files.filter((f) => f.endsWith('.html') || f.endsWith('.js')).map((f) => readFile(f, 'utf8'))
  )
  for (const needle of ['download_all.php', '/repository']) {
    if (!html.some((c) => c.includes(needle))) {
      fail(`Landing page bundle is missing ${needle} — check NUXT_PUBLIC_REPOSITORY_BASE.`)
    }
  }

  // Credentials belong only in the generated config.
  const leakPattern = /u895763689|Admin@2025/
  for (const file of files) {
    if (path.basename(file) === 'config.local.php') continue
    if (!/\.(php|js|html|css|json|ya?ml|md|txt)$/i.test(file)) continue
    const contents = await readFile(file, 'utf8')
    if (leakPattern.test(contents)) {
      fail(`Credential-looking string found in ${path.relative(outDir, file)}`)
    }
  }

  const bytes = (await Promise.all(files.map(async (f) => (await stat(f)).size))).reduce((a, b) => a + b, 0)
  return { count: files.length, mb: (bytes / 1024 / 1024).toFixed(1) }
}

await assemble()
if (!skipConfig) await writeProductionConfig()
const { count, mb } = await validate()

console.log(`\n  Package ready: deploy/  (${count} files, ${mb} MB)`)
console.log(`  Landing page at /, repository at /repository${skipConfig ? '\n  Config skipped (--skip-config)' : ''}\n`)
