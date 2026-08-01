<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Namespaced Repository base class (PSR-4 shim)
 * 
 * Bridges legacy core/Repository.php (global namespace) with PSR-4 autoloading.
 * Repository classes using `use App\Core\Repository` will resolve correctly.
 */
abstract class Repository extends \Repository
{
    // All functionality inherited from \Repository
}

