<?php

namespace App\Services\Query;

use App\Interfaces\ConnectionInterface;
use App\Interfaces\QueryInterface;

abstract class Query implements QueryInterface
{
    protected array $paginate;
    protected bool $crawling = false;

    public function __construct(
        protected ConnectionInterface $connection
    )
    {
    }

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

}
