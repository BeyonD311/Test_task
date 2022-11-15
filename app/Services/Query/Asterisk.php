<?php

namespace App\Services\Query;

use App\Services\Factory\Dto as DtoFactory;
use App\Services\Dto\File;
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
            ['cdr.recordingfile', '!=', ""]
        ];
        if($this->crawling) {
            foreach ($this->crawlingPages($where) as $items) {
                foreach ($items as $item) {
                    yield $item;
                }
            }
        }

        return $this->iteration($where);
	}

    /**
     * @param $where
     * @return array
     */
	private function makeQuery($where)
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
        $result = [];
        while (true) {
            $items = $this->makeQuery($where);
            if (empty($items)) {
                break;
            }
            /**@var \Illuminate\Pagination\LengthAwarePaginator $items*/
            foreach ($items->items() as $item) {
                $file = env('ASTERISK_DIR').date('Y/m/d/', strtotime($item->calldate)).$item->recordingfile;
                $prop = [
                    'file' => $file,
                    'src' => $item->src,
                    'dst' => $item->dst,
                    'duration' => $item->duration,
                    'uniqueid' => $item->uniqueid,
                    'calldate' => $item->calldate,
                    'connection_id' => $this->connection->getParam('id'),
                    'outputName' => $this->outputName($item->recordingfile),
                    'downloadMethod' => $this->connection->getParam('type_connection'),
                    'connection_name' => "Asterisk",
                    'options' => [
                        'server' => $this->connection->getParam('server')
                    ],
                    'queue' => 'Asterisk'
                ];
                $result[] = DtoFactory::getInstance(File::class, $prop);
            }
            yield $result;
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

    private function outputName($file): string
    {
        $name = explode(".", $file);
        $expansion = array_pop($name);
        return implode(".", $name)."-".$this->connection->getParam('id').".$expansion";
    }

    public function getNumbersOfRecords(string $from, string $to): int
    {
        $where = [
            ['cdr.calldate', '>=', $from],
            ['cdr.calldate', '<=', $to],
            ['cdr.disposition', '=', "ANSWERED"],
            ['cdr.recordingfile', '!=', ""]
        ];

        $query = $this->makeQuery($where);

        return $query->total();
    }
}
