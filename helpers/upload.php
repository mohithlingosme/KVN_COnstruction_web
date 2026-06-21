<?php
/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| SECURE FILE UPLOAD SYSTEM
|--------------------------------------------------------------------------
| File:
| /helpers/upload.php
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| BOOTSTRAP SAFETY
|--------------------------------------------------------------------------
*/

if (!defined('UPLOAD_BASE_PATH')) {
    define('UPLOAD_BASE_PATH', rtrim(ROOT_PATH, '/\\') . '/uploads/');
}

if (!defined('MAX_IMAGE_SIZE')) {
    define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);
}

if (!defined('MAX_DOCUMENT_SIZE')) {
    define('MAX_DOCUMENT_SIZE', 10 * 1024 * 1024);
}

if (!defined('ALLOWED_IMAGE_TYPES')) {
    define('ALLOWED_IMAGE_TYPES', [
        'image/jpeg',
        'image/png',
        'image/webp',
    ]);
}

if (!defined('ALLOWED_DOCUMENT_TYPES')) {
    define('ALLOWED_DOCUMENT_TYPES', [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ]);
}

if (!defined('BLOCKED_EXTENSIONS')) {
    define('BLOCKED_EXTENSIONS', [
        'php',
        'phtml',
        'phar',
        'exe',
        'sh',
        'bat',
        'js',
        'cmd',
        'msi',
    ]);
}

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('normalizeUploadFolder')) {
    function normalizeUploadFolder($folder)
    {
        $folder = trim((string)$folder);

        if ($folder === '') {
            return 'general';
        }

        $folder = str_replace(['\\', '..', '//'], ['/', '', '/'], $folder);
        $folder = preg_replace('/[^a-zA-Z0-9\/_-]/', '', $folder);
        $folder = trim($folder, '/');

        return $folder !== '' ? $folder : 'general';
    }
}

if (!function_exists('generateSecureFilename')) {
    function generateSecureFilename($originalName)
    {
        $extension = strtolower((string) pathinfo((string)$originalName, PATHINFO_EXTENSION));
        $extension = preg_replace('/[^a-z0-9]/', '', $extension);

        $random = bin2hex(random_bytes(16));
        $timestamp = time();

        return 'kvn_' . $random . '_' . $timestamp . ($extension !== '' ? '.' . $extension : '');
    }
}

if (!function_exists('validateFileExtension')) {
    function validateFileExtension($filename, $allowedExtensions = [])
    {
        $extension = strtolower((string) pathinfo((string)$filename, PATHINFO_EXTENSION));
        $extension = preg_replace('/[^a-z0-9]/', '', $extension);

        if ($extension === '') {
            return false;
        }

        if (in_array($extension, BLOCKED_EXTENSIONS, true)) {
            return false;
        }

        if (!empty($allowedExtensions)) {
            return in_array($extension, $allowedExtensions, true);
        }

        return true;
    }
}

if (!function_exists('validateMimeType')) {
    function validateMimeType($file, $allowedTypes)
    {
        if (
            empty($file['tmp_name']) ||
            !is_string($file['tmp_name']) ||
            !is_uploaded_file($file['tmp_name'])
        ) {
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo === false) {
            return false;
        }

        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($mime === false) {
            return false;
        }

        return in_array($mime, $allowedTypes, true);
    }
}

if (!function_exists('validateImage')) {
    function validateImage($file)
    {
        if (empty($file['tmp_name']) || !is_string($file['tmp_name'])) {
            return false;
        }

        return getimagesize($file['tmp_name']) !== false;
    }
}

if (!function_exists('ensureUploadDirectory')) {
    function ensureUploadDirectory($directory)
    {
        $directory = rtrim((string)$directory, '/\\');

        if ($directory === '') {
            return false;
        }

        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
                return false;
            }
        }

        $htaccess = $directory . DIRECTORY_SEPARATOR . '.htaccess';

        if (!file_exists($htaccess)) {
            $rules = <<<HTACCESS
<FilesMatch "\.(php|phtml|phar)$">
    Require all denied
</FilesMatch>
HTACCESS;

            @file_put_contents($htaccess, $rules . PHP_EOL);
        }

        $index = $directory . DIRECTORY_SEPARATOR . 'index.html';
        if (!file_exists($index)) {
            @file_put_contents($index, '');
        }

        return true;
    }
}

if (!function_exists('logUploadSecurityEvent')) {
    function logUploadSecurityEvent($action, $severity, $detail)
    {
        if (function_exists('logSecurityEvent')) {
            logSecurityEvent(
                $_SESSION['user_id'] ?? null,
                (string)$action,
                (string)$severity,
                (string)$detail
            );
        }
    }
}

if (!function_exists('buildUploadedPath')) {
    function buildUploadedPath($folder, $filename)
    {
        $folder = normalizeUploadFolder($folder);
        $filename = basename((string)$filename);

        return 'uploads/' . $folder . '/' . $filename;
    }
}

if (!function_exists('isPathInsideUploads')) {
    function isPathInsideUploads($path)
    {
        $uploadsRoot = realpath(UPLOAD_BASE_PATH);
        $targetPath = realpath($path);

        if ($uploadsRoot === false || $targetPath === false) {
            return false;
        }

        $uploadsRoot = rtrim(str_replace('\\', '/', $uploadsRoot), '/') . '/';
        $targetPath = str_replace('\\', '/', $targetPath);

        return strpos($targetPath . (is_dir($targetPath) ? '/' : ''), $uploadsRoot) === 0;
    }
}

/*
|--------------------------------------------------------------------------
| UPLOAD IMAGE
|--------------------------------------------------------------------------
*/

if (!function_exists('uploadImage')) {
    function uploadImage($file, $folder = 'general')
    {
        if (
            empty($file) ||
            !is_array($file) ||
            !isset($file['error'], $file['tmp_name'], $file['size'], $file['name']) ||
            (int)$file['error'] !== UPLOAD_ERR_OK
        ) {
            return [
                'success' => false,
                'message' => 'Invalid upload.',
            ];
        }

        if ((int)$file['size'] > MAX_IMAGE_SIZE) {
            return [
                'success' => false,
                'message' => 'Image exceeds size limit.',
            ];
        }

        if (!validateFileExtension($file['name'])) {
            if (function_exists('suspiciousActivity')) {
                suspiciousActivity('Blocked dangerous upload: ' . (string)$file['name']);
            }

            logUploadSecurityEvent('blocked_upload', 'warning', (string)$file['name']);

            return [
                'success' => false,
                'message' => 'Invalid file extension.',
            ];
        }

        if (!validateMimeType($file, ALLOWED_IMAGE_TYPES)) {
            return [
                'success' => false,
                'message' => 'Invalid image type.',
            ];
        }

        if (!validateImage($file)) {
            return [
                'success' => false,
                'message' => 'Corrupted image.',
            ];
        }

        $folder = normalizeUploadFolder($folder);
        $uploadDir = rtrim(UPLOAD_BASE_PATH, '/\\') . DIRECTORY_SEPARATOR . $folder;

        if (!ensureUploadDirectory($uploadDir)) {
            return [
                'success' => false,
                'message' => 'Unable to create upload directory.',
            ];
        }

        $filename = generateSecureFilename($file['name']);
        $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            logUploadSecurityEvent('image_uploaded', 'info', $filename);

            return [
                'success' => true,
                'filename' => $filename,
                'path' => buildUploadedPath($folder, $filename),
            ];
        }

        return [
            'success' => false,
            'message' => 'Upload failed.',
        ];
    }
}

/*
|--------------------------------------------------------------------------
| UPLOAD DOCUMENT
|--------------------------------------------------------------------------
*/

if (!function_exists('uploadDocument')) {
    function uploadDocument($file, $folder = 'documents')
    {
        if (
            empty($file) ||
            !is_array($file) ||
            !isset($file['error'], $file['tmp_name'], $file['size'], $file['name']) ||
            (int)$file['error'] !== UPLOAD_ERR_OK
        ) {
            return [
                'success' => false,
                'message' => 'Invalid document.',
            ];
        }

        if ((int)$file['size'] > MAX_DOCUMENT_SIZE) {
            return [
                'success' => false,
                'message' => 'Document exceeds limit.',
            ];
        }

        if (!validateFileExtension($file['name'])) {
            if (function_exists('suspiciousActivity')) {
                suspiciousActivity('Blocked document upload: ' . (string)$file['name']);
            }

            logUploadSecurityEvent('blocked_document_upload', 'warning', (string)$file['name']);

            return [
                'success' => false,
                'message' => 'Invalid extension.',
            ];
        }

        if (!validateMimeType($file, ALLOWED_DOCUMENT_TYPES)) {
            return [
                'success' => false,
                'message' => 'Invalid document type.',
            ];
        }

        $folder = normalizeUploadFolder($folder);
        $uploadDir = rtrim(UPLOAD_BASE_PATH, '/\\') . DIRECTORY_SEPARATOR . $folder;

        if (!ensureUploadDirectory($uploadDir)) {
            return [
                'success' => false,
                'message' => 'Unable to create upload directory.',
            ];
        }

        $filename = generateSecureFilename($file['name']);
        $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            logUploadSecurityEvent('document_uploaded', 'info', $filename);

            return [
                'success' => true,
                'filename' => $filename,
                'path' => buildUploadedPath($folder, $filename),
            ];
        }

        return [
            'success' => false,
            'message' => 'Upload failed.',
        ];
    }
}

/*
|--------------------------------------------------------------------------
| DELETE FILE
|--------------------------------------------------------------------------
*/

if (!function_exists('deleteUploadedFile')) {
    function deleteUploadedFile($path)
    {
        $path = ltrim((string)$path, '/\\');

        if ($path === '') {
            return false;
        }

        $fullPath = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . $path;

        if (!file_exists($fullPath)) {
            return false;
        }

        if (!isPathInsideUploads($fullPath)) {
            if (function_exists('suspiciousActivity')) {
                suspiciousActivity('Illegal file delete attempt');
            }

            logUploadSecurityEvent('illegal_delete_attempt', 'warning', $path);

            return false;
        }

        if (@unlink($fullPath)) {
            logUploadSecurityEvent('file_deleted', 'warning', $path);
            return true;
        }

        return false;
    }
}

/*
|--------------------------------------------------------------------------
| FILE URL
|--------------------------------------------------------------------------
*/

if (!function_exists('uploadedFileUrl')) {
    function uploadedFileUrl($path)
    {
        $path = ltrim((string)$path, '/\\');

        return rtrim(APP_URL, '/') . '/' . $path;
    }
}

/*
|--------------------------------------------------------------------------
| FORMAT FILE SIZE
|--------------------------------------------------------------------------
*/

if (!function_exists('formatFileSize')) {
    function formatFileSize($bytes)
    {
        $bytes = (int)$bytes;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }
}

/*
|--------------------------------------------------------------------------
| CLEANUP OLD TEMP FILES
|--------------------------------------------------------------------------
*/

if (!function_exists('cleanupTemporaryUploads')) {
    function cleanupTemporaryUploads($hours = 24)
    {
        $hours = max(1, (int)$hours);
        $directory = rtrim(UPLOAD_BASE_PATH, '/\\') . DIRECTORY_SEPARATOR . 'temp';

        if (!file_exists($directory) || !is_dir($directory)) {
            return;
        }

        $files = scandir($directory);
        if ($files === false) {
            return;
        }

        $threshold = strtotime('-' . $hours . ' hours');

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $directory . DIRECTORY_SEPARATOR . $file;

            if (
                is_file($filePath) &&
                filemtime($filePath) !== false &&
                filemtime($filePath) < $threshold
            ) {
                @unlink($filePath);
            }
        }
    }
}