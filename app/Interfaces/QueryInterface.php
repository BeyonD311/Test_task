<?php

namespace App\Interfaces;

interface QueryInterface
{
    /**
     * @getItems принимиет два параметра даты
     * @param string $from - начала
     * @param string $to - конец
     * @return \Generator
     */
    public function getItems(string $from, string $to):\Generator;
}
