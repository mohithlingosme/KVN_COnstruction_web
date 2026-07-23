<?php

declare(strict_types=1);

class FakePDO
{
    /** @var callable|null */
    public $onPrepare = null;

    /** @var array<int,array{query:string,params:array}> */
    public array $executed = [];

    public function __construct(?callable $onPrepare = null)
    {
        $this->onPrepare = $onPrepare;
    }

    public function prepare(string $query)
    {
        $fake = new FakeStatement($query, $this);
        return $fake;
    }

    public function beginTransaction(): void {}
    public function commit(): void {}
    public function rollBack(): void {}

    public function query(string $query)
    {
        // For AdminController dashboard counts.
        $stmt = new class($query, $this) {
            private string $q;
            private FakePDO $pdo;
            public function __construct(string $q, FakePDO $pdo) { $this->q = $q; $this->pdo = $pdo; }
            public function fetch($mode = null)
            {
                // default empty counts
                return [];
            }
        };
        return $stmt;
    }
}

