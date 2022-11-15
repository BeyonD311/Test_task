<?php

namespace App\Services\Query;

use App\Services\Interfaces\Connection;
use App\Services\Interfaces\QueryInterface;

class ContextQuery
{
    private QueryInterface $context;

    public function setOptions($page = 1, $size = 1000)
    {
        $this->context
            ->setPaginate($page, $size)
            ->onCrawlingPages();
    }

    public function setContext(QueryInterface $context, Connection $connection)
    {
        $this->context = $context;
        $this->context->setConnection($connection);
    }

    /**
     * @param string $from
     * @param string $to
     * @return \Generator
     */
    public function getItems(string $from, string $to): \Generator
    {
        return $this->context->getItems($from, $to);
    }

    /**
     * @param string $from - дата старт
     * @param string $to - дата конец
     * @return int
     */
    public function getNumbersOfRecords(string $from, string $to): int
    {
        return $this->context->getNumbersOfRecords($from, $to);
    }
}
