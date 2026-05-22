<?php

// =====================================
// FORMAT CURRENCY
// =====================================

function formatCurrency(
    $amount,
    $symbol = '₹'
)
{
    return $symbol .
    number_format(
        $amount,
        2
    );
}

// =====================================
// FORMAT DATE
// =====================================

function formatDate(
    $date,
    $format = 'd M Y'
)
{
    if(empty($date)){

        return '-';
    }

    return date(
        $format,
        strtotime($date)
    );
}

// =====================================
// FORMAT DATETIME
// =====================================

function formatDateTime(
    $date,
    $format = 'd M Y h:i A'
)
{
    if(empty($date)){

        return '-';
    }

    return date(
        $format,
        strtotime($date)
    );
}

// =====================================
// LIMIT TEXT
// =====================================

function limitText(
    $text,
    $limit = 100
)
{
    $text =
    strip_tags($text);

    if(strlen($text) > $limit){

        return substr(
            $text,
            0,
            $limit
        ) . '...';
    }

    return $text;
}

// =====================================
// FORMAT PHONE
// =====================================

function formatPhone($phone)
{
    return preg_replace(
        '/[^0-9]/',
        '',
        $phone
    );
}

// =====================================
// STATUS BADGE
// =====================================

function statusBadge($status)
{
    $class = 'badge-secondary';

    switch($status){

        case 'active':
        case 'completed':
        case 'approved':

            $class = 'badge-success';
            break;

        case 'pending':
        case 'processing':

            $class = 'badge-warning';
            break;

        case 'inactive':
        case 'rejected':
        case 'cancelled':

            $class = 'badge-danger';
            break;
    }

    return '

        <span class="badge-status ' .
        $class .
        '">

            ' .
            ucfirst($status) .
        '

        </span>
    ';
}

// =====================================
// TIME AGO
// =====================================

function timeAgo($datetime)
{
    $time =
    strtotime($datetime);

    $diff =
    time() - $time;

    if($diff < 60){

        return 'Just now';
    }

    if($diff < 3600){

        return floor($diff / 60)
        . ' mins ago';
    }

    if($diff < 86400){

        return floor($diff / 3600)
        . ' hours ago';
    }

    if($diff < 2592000){

        return floor($diff / 86400)
        . ' days ago';
    }

    return formatDate($datetime);
}

// =====================================
// FORMAT FILE SIZE
// =====================================

function formatBytes($bytes)
{
    if($bytes >= 1073741824){

        return number_format(
            $bytes / 1073741824,
            2
        ) . ' GB';
    }

    if($bytes >= 1048576){

        return number_format(
            $bytes / 1048576,
            2
        ) . ' MB';
    }

    if($bytes >= 1024){

        return number_format(
            $bytes / 1024,
            2
        ) . ' KB';
    }

    return $bytes . ' B';
}

// =====================================
// BOOLEAN LABEL
// =====================================

function booleanLabel($value)
{
    return $value

    ? 'Yes'

    : 'No';
}
?>