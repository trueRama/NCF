<?php
/**
 * Streams every file in one repository as a single zip.
 *
 * GET /download_all.php?category=reports[&event=<event_code>]
 *
 * Omitting `event` zips the category across all events, which is what the
 * landing page cards link to. The zip is built per request so it always
 * reflects what an administrator has just uploaded.
 */

require_once __DIR__ . '/includes/config.php';

if (!class_exists('ZipArchive')) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Bulk download unavailable: the PHP zip extension is not enabled on this server.');
}

$category = sanitizeCategory($_GET['category'] ?? null);

$eventId = null;
$eventCode = $_GET['event'] ?? null;
if ($eventCode) {
    $event = getEventByCode($pdo, $eventCode);
    if (!$event) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        exit('Event not found or has been deactivated.');
    }
    $eventId = $event['id'];
}

$files = getCategoryFiles($pdo, $category, $eventId);

if (empty($files)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    exit('There are no files in ' . categoryLabel($category) . ' yet.');
}

$uploadsDir = realpath(__DIR__ . '/uploads');
if ($uploadsDir === false) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Uploads directory is missing.');
}

$tmpPath = tempnam(sys_get_temp_dir(), 'ncf_zip_');
if ($tmpPath === false) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Could not create the archive.');
}

$zip = new ZipArchive();
if ($zip->open($tmpPath, ZipArchive::OVERWRITE) !== true) {
    unlink($tmpPath);
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Could not create the archive.');
}

$usedNames = [];
$added = 0;

foreach ($files as $file) {
    // basename() plus the realpath check keeps a tampered DB row inside uploads/.
    $stored = $uploadsDir . DIRECTORY_SEPARATOR . basename($file['filename']);
    $resolved = realpath($stored);

    if ($resolved === false || strncmp($resolved, $uploadsDir . DIRECTORY_SEPARATOR, strlen($uploadsDir) + 1) !== 0) {
        continue;
    }

    $zip->addFile($resolved, uniqueEntryName($file, $usedNames));
    $added++;
}

$zip->close();

if ($added === 0) {
    // ZipArchive discards an archive it closed with no entries, so it may be gone.
    if (is_file($tmpPath)) {
        unlink($tmpPath);
    }
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    exit('The files in ' . categoryLabel($category) . ' are no longer available on disk.');
}

$downloadName = zipFileName($category, $eventCode);

while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . filesize($tmpPath));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

readfile($tmpPath);
unlink($tmpPath);
exit;

/** Keeps the delegate-facing name but avoids collisions inside the archive. */
function uniqueEntryName(array $file, array &$usedNames): string
{
    $name = basename($file['original_name'] ?: $file['filename']);
    $name = preg_replace('/[^\w\-. ]+/u', '_', $name) ?: 'file';

    if (!isset($usedNames[strtolower($name)])) {
        $usedNames[strtolower($name)] = true;
        return $name;
    }

    $extension = pathinfo($name, PATHINFO_EXTENSION);
    $stem = pathinfo($name, PATHINFO_FILENAME);
    $suffix = 2;

    do {
        $candidate = $stem . ' (' . $suffix . ')' . ($extension ? '.' . $extension : '');
        $suffix++;
    } while (isset($usedNames[strtolower($candidate)]));

    $usedNames[strtolower($candidate)] = true;
    return $candidate;
}

function zipFileName(string $category, ?string $eventCode): string
{
    $parts = ['ncf', str_replace('_', '-', $category)];
    if ($eventCode) {
        $parts[] = preg_replace('/[^A-Za-z0-9\-]+/', '-', $eventCode);
    }
    $parts[] = date('Y-m-d');

    return implode('-', $parts) . '.zip';
}
