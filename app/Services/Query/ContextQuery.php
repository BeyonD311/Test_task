<?php

namespace App\Services\Query;

use App\Services\Interfaces\Connection;

class ContextQuery
{
    private Query $context;

    public function setOptions()
    {
        $this->context
            ->setPaginate(1, 100)
            ->onCrawlingPages();
    }

    public function setContext(Query $context, Connection $connection)
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
}
