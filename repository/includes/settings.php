<?php
/**
 * Configuration with no database dependency, so pages that only need settings
 * (the repository splash page) can load it without opening a connection.
 */

/**
 * Which environment this request is running in.
 *
 * Set APP_ENV to decide explicitly. Otherwise the hostname decides: anything
 * that looks like a developer machine is 'local', everything else is
 * 'production'. Defaulting an unknown host to production is deliberate — it
 * fails closed rather than exposing errors on a live site.
 */
function ncfEnvironment(): string
{
    static $environment = null;
    if ($environment !== null) {
        return $environment;
    }

    $explicit = getenv('APP_ENV');
    if (is_string($explicit) && trim($explicit) !== '') {
        // Control panels happily store "local " with a trailing space.
        $environment = strtolower(trim($explicit)) === 'local' ? 'local' : 'production';
        return $environment;
    }

    // Strip any :port before matching.
    $host = strtolower((string) strtok($_SERVER['HTTP_HOST'] ?? '', ':'));

    $isLocal = $host === ''                       // CLI: setup.php, migrations
        || in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]'], true)
        || str_starts_with($host, '192.168.')
        || str_ends_with($host, '.local')
        || str_ends_with($host, '.test')
        || str_ends_with($host, '.localhost');

    $environment = $isLocal ? 'local' : 'production';
    return $environment;
}

function isProduction(): bool
{
    return ncfEnvironment() === 'production';
}

function ncfConfig(): array
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }

    $environment = ncfEnvironment();

    // XAMPP defaults, so a fresh checkout runs locally with no config file.
    $defaults = [
        'db_host'             => 'localhost',
        'db_name'             => 'ncf_repository',
        'db_user'             => $environment === 'local' ? 'root' : '',
        'db_pass'             => '',
        'admin_username'      => 'admin',
        'admin_password_hash' => '',
        'landing_page_url'    => '',
    ];

    $config = array_merge($defaults, loadLocalConfig($environment), configFromEnvironment());
    return $config;
}

/**
 * Reads includes/config.local.php. The file may be flat (applies everywhere) or
 * keyed by environment with an optional 'shared' block:
 *
 *   return [
 *       'shared'     => ['admin_username' => 'admin'],
 *       'local'      => ['db_user' => 'root', 'db_pass' => ''],
 *       'production' => ['db_name' => '...', 'db_user' => '...', 'db_pass' => '...'],
 *   ];
 */
function loadLocalConfig(string $environment): array
{
    $localFile = __DIR__ . '/config.local.php';
    if (!is_readable($localFile)) {
        return [];
    }

    $loaded = require $localFile;
    if (!is_array($loaded)) {
        return [];
    }

    $isKeyedByEnvironment = isset($loaded['local']) || isset($loaded['production']) || isset($loaded['shared']);
    if (!$isKeyedByEnvironment) {
        return $loaded;
    }

    return array_merge(
        is_array($loaded['shared'] ?? null) ? $loaded['shared'] : [],
        is_array($loaded[$environment] ?? null) ? $loaded[$environment] : []
    );
}

/** Environment variables win over the config file, for hosts that set them. */
function configFromEnvironment(): array
{
    return array_filter([
        'db_host'             => getenv('DB_HOST') ?: null,
        'db_name'             => getenv('DB_NAME') ?: null,
        'db_user'             => getenv('DB_USER') ?: null,
        'db_pass'             => getenv('DB_PASS') !== false ? getenv('DB_PASS') : null,
        'admin_username'      => getenv('ADMIN_USERNAME') ?: null,
        'admin_password_hash' => getenv('ADMIN_PASSWORD_HASH') ?: null,
        'landing_page_url'    => getenv('LANDING_PAGE_URL') ?: null,
    ], static fn($value) => $value !== null);
}

/**
 * Where the logo links back to: the Forum landing page, which sits at the web
 * root above /repository. The relative default resolves correctly once
 * deployed (https://ncf.miichub.com/), so no domain is hardcoded here.
 *
 * Locally the web root has no landing page — the Nuxt site runs separately —
 * so set `landing_page_url` (or LANDING_PAGE_URL) to an absolute URL there.
 *
 * @param string $relativeFallback Hop from the calling page up to the web root.
 */
function landingPageUrl(string $relativeFallback = '../../'): string
{
    $configured = ncfConfig()['landing_page_url'];
    return $configured !== '' ? $configured : $relativeFallback;
}
