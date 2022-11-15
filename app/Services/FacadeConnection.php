<?php

namespace App\Services;

use App\Services\DTO\File;
use App\Services\DTO\Connection;
use App\Services\Factory\ConnectionFactory;
use App\Services\Factory\QueryFactory;
use App\Services\Interfaces\QueryInterface;
use \App\Services\Interfaces\Connection as ConnectionInterface;
use App\Services\Query\ContextQuery;

class FacadeConnection
{
    /**
     * @param Connection $dto
     * @return QueryInterface
     * @throws \Exception
     */
    public static function getQueryInstance(Connection $dto): QueryInterface
    {
        return QueryFactory::getInstance($dto);
    }

    /**
     * @param Connection $dto
     * @return Interfaces\Connection
     * @throws \App\Exceptions\Connection
     * @throws \ReflectionException
     */
    public static function getConnection(Connection $dto): ConnectionInterface
    {
        return ConnectionFactory::getInstance($dto);
    }

    /**
     * @param QueryInterface $query
     * @param ConnectionInterface $connection
     * @return ContextQuery
     */
    public static function makeQueryContext(QueryInterface $query, ConnectionInterface $connection): ContextQuery
    {
        // Создание выборки
        $queryContext = new ContextQuery();
        $queryContext->setContext($query, $connection);
        $queryContext->setOptions();
        return $queryContext;
    }

    /**
     * @return Downloading\DownloadFile
     */
    public static function makeDownload(): \App\Services\Downloading\DownloadFile
    {
        return (new \App\Services\Downloading\DownloadFile());
    }
}
