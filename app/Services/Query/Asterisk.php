<?php

namespace App\Services\Query;

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

        return $this->crawlingPages(1, 100, $where);
	}

	private function makeQuery($page, $count, $where)
    {
        $items = $this->connection->connection()->where($where)
            ->groupBy('cdr.linkedid')
            ->orderBy('cdr.calldate', 'DESC')
            ->paginate($count, page: $page);
        if($page > $items->lastPage()) {
            return [];
        }
        return $items->items();
    }

    /**
     * Вятягивает записи кусками
     * @param int $page
     * @param int $count
     * @param array $where
     * @return \Generator
     */
    private function crawlingPages(int $page,int $count,array $where): \Generator
    {
        while (true) {
            $items = $this->makeQuery($page, $count, $where);
            if (empty($items)) {
                break;
            }
            foreach ($items as $item) {
                yield $item;
            }
            $page++;
        }
    }
}
