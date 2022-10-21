<?php

namespace App\Services\Query;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class Asterisk extends Query
{
	/**
	 * @inheritDoc
	 */
	public function getItems(string $from, string $to): \Generator
	{

        /**
         * @var \Illuminate\Database\Query\Builder $items
         */
        $where = [
            ['cdr.calldate', '>=', $from],
            ['cdr.calldate', '<=', $to],
            ['cdr.disposition', '=', "ANSWERED"],
            ['cdr.recordingfile', '!=', null]
        ];
        if($this->crawling) {
            return $this->crawlingPages($where);
        }

        return $this->iteration($where);
	}

    /**
     * @param $where
     * @return array
     */
	private function makeQuery($where): LengthAwarePaginator
    {
        $items = $this->connection->connection()->where($where)
            ->groupBy('cdr.linkedid')
            ->orderBy('cdr.calldate', 'DESC')
            ->paginate($this->paginate['size'], page: $this->paginate['page']);
        if($this->paginate['page'] > $items->lastPage()) {
            return [];
        }
        return $items;
    }

    /**
     * Вятягивает записи кусками
     * @param array $where
     * @return \Generator
     */
    private function crawlingPages(array $where): \Generator
    {
        while (true) {
            $items = $this->makeQuery($where);
            if (empty($items)) {
                break;
            }
            foreach ($items->items() as $item) {
                yield $item->recordingfile => $item;
            }
            $this->paginate['page']++;
        }
    }

    private function iteration(array $where): \Generator
    {
        $items = $this->makeQuery($where);
        foreach ($items as $item) {
            yield $item->recordingfile => $item;
        }
    }

    public function getNumbersOfRecords(string $from, string $to): int
    {
        $where = [
            ['cdr.calldate', '>=', $from],
            ['cdr.calldate', '<=', $to],
            ['cdr.disposition', '=', "ANSWERED"],
            ['cdr.recordingfile', '!=', null]
        ];

        $query = $this->makeQuery($where);

        return $query->total();
    }
}
