<?php

namespace App\Interfaces;

interface QueryInterface
{
    /**
     * Пагинация для добавления пагинации
     * @param $page
     * @param $size
     * @return $this
     */
    public function setPaginate($page, $size): static;

    public function getPaginate(): array;
    /**
     * @getItems принимиет два параметра даты
     * @param string $from - начала
     * @param string $to - конец
     * @return \Generator
     */
    public function getItems(string $from, string $to):\Generator;

    /**
     * Влючить обход всех страниц
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
