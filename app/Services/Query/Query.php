<?php

namespace App\Services\Query;

use App\Services\Interfaces\Connection;
use App\Services\Interfaces\QueryInterface;

abstract class Query implements QueryInterface
{
    protected array $paginate;
    protected bool $crawling = false;
    protected Connection $connection;

    /**
     * @inheritDoc
     * @return $this
     */
    public function onCrawlingPages(): static
    {
        $this->crawling = true;
        return $this;
    }

    public function offCrawlingPages(): static
    {
        $this->crawling = false;
        return $this;
    }

    public function setPaginate($page, $size): static
    {
        $this->paginate['page'] = $page;
        $this->paginate['size'] = $size;
        return $this;
    }

    public function getPaginate(): array
    {
        return $this->paginate;
    }

    public function setConnection(Connection $connection): QueryInterface
    {
        $this->connection = $connection;
        return $this;
    }

}
