<?php

declare(strict_types=1);

class FakeStatement
{
    private string $query;
    private FakePDO $pdo;
    private array $params = [];

    /** @var callable|null */
    private $onFetch = null;

    public function __construct(string $query, FakePDO $pdo)
    {
        $this->query = $query;
        $this->pdo = $pdo;
    }

    public function execute(array $params = []): bool
    {
        $this->params = $params;
        $this->pdo->executed[] = [
            'query' => $this->query,
            'params' => $params,
        ];
        return true;
    }

    public function fetch($mode = null)
    {
        if (is_callable($this->onFetch)) {
            return ($this->onFetch)($this->params);
        }
        return null;
    }

    public function fetchAll($mode = null): array
    {
        // Default: empty list
        return [];
    }

    public function bindValue(string $param, $value, ?int $type = null): void
    {
        $this->params[$param] = $value;
    }

    public function setOnFetch(callable $fn): void
    {
        $this->onFetch = $fn;
    }
}

