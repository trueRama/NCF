<?php
/**
 * Local overrides. Copy to config.local.php and edit — that file is gitignored.
 *
 * The environment is detected automatically: localhost / 127.0.0.1 / *.test /
 * *.local / CLI count as 'local', any other hostname is 'production'. Set the
 * APP_ENV environment variable to override that.
 *
 * Keys may also be supplied as environment variables (DB_HOST, DB_NAME,
 * DB_USER, DB_PASS, ADMIN_USERNAME, ADMIN_PASSWORD_HASH, LANDING_PAGE_URL),
 * which take precedence over this file.
 */

return [
    // Applies in both environments.
    'shared' => [
        'admin_username' => 'admin',

        // Generate with: php -r "echo password_hash('your-password', PASSWORD_DEFAULT);"
        'admin_password_hash' => '',

        // Where the logo links back to. Empty resolves to the web root above
        // /repository, which is the Forum landing page once deployed. Locally
        // there is no landing page at the root, so set an absolute URL such as
        // 'https://ncf.miichub.com/' or 'http://localhost:3000/'.
        'landing_page_url' => '',
    ],

    // XAMPP. These match the built-in defaults, so this block is optional.
    'local' => [
        'db_host' => 'localhost',
        'db_name' => 'ncf_repository',
        'db_user' => 'root',
        'db_pass' => '',
    ],

    // The live host. CI writes this block from repository secrets, so you only
    // need it here if you deploy by hand.
    'production' => [
        'db_host' => 'localhost',
        'db_name' => '',
        'db_user' => '',
        'db_pass' => '',
    ],
];
