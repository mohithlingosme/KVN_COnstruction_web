<?php

declare(strict_types=1);

function limitText(string $text, int $length = 120): string {
    $text = trim(strip_tags($text));

    if (mb_strlen($text) <= $length) {
        return $text;
    }

    return mb_substr($text, 0, $length) . '...';
}

