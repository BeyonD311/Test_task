<?php

namespace App\Services;

use App\Exceptions\Config;
use App\Interfaces\Host;

final class Driver
{
    private array $config;
    private string $name;
    public function __construct(
        protected Host $db
    ){
        app("db");
        $this->config = config('database')['connections'];
    }

    /**
     * @param string|null $driver
     * @throws Config
     */
    public function setDriver(string $name, string $driver, string $database)
    {
        app('db');
        if(empty($this->newConfig = config('database')['connections'][$driver])) {
            throw new Config("Драйвер не установлен");
        }
        if(isset($this->config[$name])) {
            $newConfig = $this->config[$driver];
        }

        $this->name = $name;
        $newConfig['driver'] = $driver;
        $newConfig['host'] = $this->db->getHost();
        $newConfig['port'] = $this->db->getPort();
        $newConfig['username'] = $this->db->getLogin();
        $newConfig['password'] = $this->db->getPass();
        $newConfig['database'] = $database;
        config(['database.connections.'.$name => $newConfig]);
    }

    public function getConfig(): string
    {
        return $this->name;
    }

}
