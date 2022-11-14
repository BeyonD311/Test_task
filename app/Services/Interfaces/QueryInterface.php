<?php

namespace App\Services\Interfaces;

interface QueryInterface
{
    public function setConnection(Connection $connection): QueryInterface;
    /**
     * Пагинация для добавления пагинации
     * @param $page
     * @param $size
     * @return QueryInterface
     */
    public function setPaginate($page, $size): QueryInterface;

    public function getPaginate(): array;

    /**
     * @getItems принимиет два параметра даты
     * @param string $from - начала
     * @param string $to - конец
     * @return \Generator
     */
    public function getItems(string $from, string $to):\Generator;

    /**
     * Включить обход всех страниц
     * @return $this
     */
    public function onCrawlingPages(): static;

    /**
     * Выключить обход всех страниц
     * @return $this
     */
    public function offCrawlingPages(): static;

    /**
     * Получение всех элементов сервиса
     * @param string $from
     * @param string $to
     * @return int
     */
    public function getNumbersOfRecords(string $from, string $to): int;
}
