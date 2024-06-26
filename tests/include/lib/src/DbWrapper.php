<?php

namespace SwooleTest;

use Swoole\Coroutine\MySQL;

class DbWrapper
{
    /**
     * @var MySQL
     */
    private $mysql;

    private $config;

    public function connect($config)
    {
        $mysql = new \mysqli();
        $res = $mysql->connect(
            $config['host'],
            $config['user'],
            $config['password'],
            $config['database'],
            $config['port'],
        );

        if (false === $res) {
            throw new RuntimeException($mysql->connect_error, $mysql->errno);
        } else {
            $this->mysql = $mysql;
            $this->config = $config;
        }

        return $res;
    }

    public function __call($name, $arguments)
    {
        // $result = $this->mysql->{$name}(...$arguments);
        // $result = call_user_func_array([$this->mysql, $name], $arguments);
        $result = $this->mysql->query($arguments[0]);
        if (false === $result) {
            if (!$this->mysql->connected) {
                $this->connect($this->config);
                return call_user_func_array([$this->mysql, $name], $arguments);
            }
            if (!empty($this->mysql->errno)) {
                throw new RuntimeException($this->mysql->error, $this->mysql->errno);
            }
        }

        return $result->fetch_all();
    }
}
