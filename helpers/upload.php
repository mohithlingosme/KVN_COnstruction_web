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
| UPLOAD CONFIG
|--------------------------------------------------------------------------
*/

define('UPLOAD_BASE_PATH', ROOT_PATH . '/uploads/');

define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);

define('MAX_DOCUMENT_SIZE', 10 * 1024 * 1024);

/*
|--------------------------------------------------------------------------
| ALLOWED IMAGE TYPES
|--------------------------------------------------------------------------
*/

define('ALLOWED_IMAGE_TYPES', [

    'image/jpeg',

    'image/png',

    'image/webp'
]);

/*
|--------------------------------------------------------------------------
| ALLOWED DOCUMENT TYPES
|--------------------------------------------------------------------------
*/

define('ALLOWED_DOCUMENT_TYPES', [

    'application/pdf',

    'application/msword',

    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
]);

/*
|--------------------------------------------------------------------------
| DANGEROUS EXTENSIONS
|--------------------------------------------------------------------------
*/

define('BLOCKED_EXTENSIONS', [

    'php',
    'phtml',
    'phar',
    'exe',
    'sh',
    'bat',
    'js',
    'cmd',
    'msi'
]);

/*
|--------------------------------------------------------------------------
| GENERATE SECURE FILE NAME
|--------------------------------------------------------------------------
*/

function generateSecureFilename($originalName)
{
    $extension = strtolower(

        pathinfo(

            $originalName,

            PATHINFO_EXTENSION
        )
    );

    return

        uniqid('kvn_', true)

        .

        '_'

        .

        time()

        .

        '.'

        .

        $extension;
}

/*
|--------------------------------------------------------------------------
| VALIDATE FILE EXTENSION
|--------------------------------------------------------------------------
*/

function validateFileExtension(

    $filename,

    $allowedExtensions = []
) {

    $extension = strtolower(

        pathinfo(

            $filename,

            PATHINFO_EXTENSION
        )
    );

    /*
    |--------------------------------------------------------------------------
    | BLOCK DANGEROUS FILES
    |--------------------------------------------------------------------------
    */

    if (

        in_array(

            $extension,

            BLOCKED_EXTENSIONS
        )
    ) {

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | ALLOWED EXTENSIONS
    |--------------------------------------------------------------------------
    */

    if (!empty($allowedExtensions)) {

        return in_array(

            $extension,

            $allowedExtensions
        );
    }

    return true;
}

/*
|--------------------------------------------------------------------------
| VALIDATE MIME TYPE
|--------------------------------------------------------------------------
*/

function validateMimeType(

    $file,

    $allowedTypes
) {

    $finfo =
    finfo_open(FILEINFO_MIME_TYPE);

    $mime =
    finfo_file(

        $finfo,

        $file['tmp_name']
    );

    finfo_close($finfo);

    return in_array(

        $mime,

        $allowedTypes
    );
}

/*
|--------------------------------------------------------------------------
| VALIDATE IMAGE FILE
|--------------------------------------------------------------------------
*/

function validateImage($file)
{
    return getimagesize(

        $file['tmp_name']
    ) !== false;
}

/*
|--------------------------------------------------------------------------
| CREATE DIRECTORY
|--------------------------------------------------------------------------
*/

function ensureUploadDirectory($directory)
{
    if (!file_exists($directory)) {

        mkdir(

            $directory,

            0755,

            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BLOCK EXECUTION
    |--------------------------------------------------------------------------
    */

    $htaccess = $directory . '/.htaccess';

    if (!file_exists($htaccess)) {

        file_put_contents(

            $htaccess,

            "
            <FilesMatch '\.(php|phtml|phar)$'>
                Deny from all
            </FilesMatch>
            "
        );
    }
}

/*
|--------------------------------------------------------------------------
| UPLOAD IMAGE
|--------------------------------------------------------------------------
*/

function uploadImage(

    $file,

    $folder = 'general'
) {

    /*
    |--------------------------------------------------------------------------
    | FILE EXISTS
    |--------------------------------------------------------------------------
    */

    if (

        empty($file)

        ||

        $file['error'] !== 0
    ) {

        return [

            'success' => false,

            'message' => 'Invalid upload.'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | FILE SIZE
    |--------------------------------------------------------------------------
    */

    if (

        $file['size']
        >
        MAX_IMAGE_SIZE
    ) {

        return [

            'success' => false,

            'message' =>
            'Image exceeds size limit.'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | EXTENSION CHECK
    |--------------------------------------------------------------------------
    */

    if (

        !validateFileExtension(

            $file['name']
        )
    ) {

        suspiciousActivity(

            'Blocked dangerous upload: '
            .
            $file['name']
        );

        return [

            'success' => false,

            'message' =>
            'Invalid file extension.'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | MIME CHECK
    |--------------------------------------------------------------------------
    */

    if (

        !validateMimeType(

            $file,

            ALLOWED_IMAGE_TYPES
        )
    ) {

        return [

            'success' => false,

            'message' =>
            'Invalid image type.'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | IMAGE VALIDATION
    |--------------------------------------------------------------------------
    */

    if (!validateImage($file)) {

        return [

            'success' => false,

            'message' =>
            'Corrupted image.'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE FOLDER
    |--------------------------------------------------------------------------
    */

    $uploadDir =
    UPLOAD_BASE_PATH . $folder;

    ensureUploadDirectory($uploadDir);

    /*
    |--------------------------------------------------------------------------
    | SECURE FILE NAME
    |--------------------------------------------------------------------------
    */

    $filename =
    generateSecureFilename(

        $file['name']
    );

    $destination =
    $uploadDir . '/' . $filename;

    /*
    |--------------------------------------------------------------------------
    | MOVE FILE
    |--------------------------------------------------------------------------
    */

    if (

        move_uploaded_file(

            $file['tmp_name'],

            $destination
        )
    ) {

        /*
        |--------------------------------------------------------------------------
        | SECURITY LOG
        |--------------------------------------------------------------------------
        */

        if (function_exists('logSecurityEvent')) {

            logSecurityEvent(

                $_SESSION['user_id'] ?? null,

                'image_uploaded',

                'info',

                $filename
            );
        }

        return [

            'success' => true,

            'filename' => $filename,

            'path' =>

                'uploads/'
                .
                $folder
                .
                '/'
                .
                $filename
        ];
    }

    return [

        'success' => false,

        'message' =>
        'Upload failed.'
    ];
}

/*
|--------------------------------------------------------------------------
| UPLOAD DOCUMENT
|--------------------------------------------------------------------------
*/

function uploadDocument(

    $file,

    $folder = 'documents'
) {

    if (

        empty($file)

        ||

        $file['error'] !== 0
    ) {

        return [

            'success' => false,

            'message' =>
            'Invalid document.'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | SIZE VALIDATION
    |--------------------------------------------------------------------------
    */

    if (

        $file['size']
        >
        MAX_DOCUMENT_SIZE
    ) {

        return [

            'success' => false,

            'message' =>
            'Document exceeds limit.'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | EXTENSION VALIDATION
    |--------------------------------------------------------------------------
    */

    if (

        !validateFileExtension(

            $file['name']
        )
    ) {

        suspiciousActivity(

            'Blocked document upload'
        );

        return [

            'success' => false,

            'message' =>
            'Invalid extension.'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | MIME VALIDATION
    |--------------------------------------------------------------------------
    */

    if (

        !validateMimeType(

            $file,

            ALLOWED_DOCUMENT_TYPES
        )
    ) {

        return [

            'success' => false,

            'message' =>
            'Invalid document type.'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE DIRECTORY
    |--------------------------------------------------------------------------
    */

    $uploadDir =
    UPLOAD_BASE_PATH . $folder;

    ensureUploadDirectory($uploadDir);

    /*
    |--------------------------------------------------------------------------
    | FILE NAME
    |--------------------------------------------------------------------------
    */

    $filename =
    generateSecureFilename(

        $file['name']
    );

    $destination =
    $uploadDir . '/' . $filename;

    /*
    |--------------------------------------------------------------------------
    | MOVE FILE
    |--------------------------------------------------------------------------
    */

    if (

        move_uploaded_file(

            $file['tmp_name'],

            $destination
        )
    ) {

        if (function_exists('logSecurityEvent')) {

            logSecurityEvent(

                $_SESSION['user_id'] ?? null,

                'document_uploaded',

                'info',

                $filename
            );
        }

        return [

            'success' => true,

            'filename' => $filename,

            'path' =>

                'uploads/'
                .
                $folder
                .
                '/'
                .
                $filename
        ];
    }

    return [

        'success' => false,

        'message' =>
        'Upload failed.'
    ];
}

/*
|--------------------------------------------------------------------------
| DELETE FILE
|--------------------------------------------------------------------------
*/

function deleteUploadedFile($path)
{
    $fullPath =
    ROOT_PATH . '/' . ltrim($path, '/');

    /*
    |--------------------------------------------------------------------------
    | SECURITY CHECK
    |--------------------------------------------------------------------------
    */

    if (

        strpos(

            realpath($fullPath),

            realpath(UPLOAD_BASE_PATH)
        ) !== 0
    ) {

        suspiciousActivity(

            'Illegal file delete attempt'
        );

        return false;
    }

    if (

        file_exists($fullPath)
    ) {

        unlink($fullPath);

        /*
        |--------------------------------------------------------------------------
        | LOG
        |--------------------------------------------------------------------------
        */

        if (function_exists('logSecurityEvent')) {

            logSecurityEvent(

                $_SESSION['user_id'] ?? null,

                'file_deleted',

                'warning',

                $path
            );
        }

        return true;
    }

    return false;
}

/*
|--------------------------------------------------------------------------
| FILE URL
|--------------------------------------------------------------------------
*/

function uploadedFileUrl($path)
{
    return APP_URL . '/../' . ltrim($path, '/');
}

/*
|--------------------------------------------------------------------------
| FORMAT FILE SIZE
|--------------------------------------------------------------------------
*/

function formatFileSize($bytes)
{
    if ($bytes >= 1073741824) {

        return number_format(

            $bytes / 1073741824,

            2
        ) . ' GB';
    }

    if ($bytes >= 1048576) {

        return number_format(

            $bytes / 1048576,

            2
        ) . ' MB';
    }

    if ($bytes >= 1024) {

        return number_format(

            $bytes / 1024,

            2
        ) . ' KB';
    }

    return $bytes . ' bytes';
}

/*
|--------------------------------------------------------------------------
| CLEANUP OLD TEMP FILES
|--------------------------------------------------------------------------
*/

function cleanupTemporaryUploads(
    $hours = 24
) {

    $directories = [

        UPLOAD_BASE_PATH . 'temp'
    ];

    foreach ($directories as $directory) {

        if (!file_exists($directory)) {

            continue;
        }

        $files = scandir($directory);

        foreach ($files as $file) {

            if (

                $file === '.'

                ||

                $file === '..'
            ) {

                continue;
            }

            $filePath =
            $directory . '/' . $file;

            if (

                is_file($filePath)

                &&

                filemtime($filePath)
                <
                strtotime(
                    '-' . $hours . ' hours'
                )
            ) {

                unlink($filePath);
            }
        }
    }
}

?>