<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Namespaced Service base class (PSR-4 shim)
 * 
 * Bridges legacy core/Service.php (global namespace) with PSR-4 autoloading.
 * All service classes using `use App\Core\Service` will inherit from this,
 * which extends the canonical \Service class in core/Service.php.
 */
abstract class Service extends \Service
{
    // All functionality inherited from \Service
}

