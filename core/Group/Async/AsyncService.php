<?php

namespace Group\Async;

class AsyncService
{   
    protected $serv;

    protected $port;

    protected $package_eof;

    protected $timeout = 1;

    protected $calls = [];

    protected $callId = 0;

    public function __construct($serv, $port, $package_eof = "\r\n")
    {   
        $this->serv = $serv;
        $this->port = $port;
        $this->package_eof = $package_eof;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function call($cmd, $data = [], $timeout = false)
    {   
        if (is_numeric($timeout)) {
            $this->timeout = $timeout;
        }

        $data = \Group\Sync\DataPack::pack($cmd, $data);
        $data .= $this->package_eof;
        $res = (yield new \Group\Async\Client\TCP($this->serv, $this->port, $data, $this->timeout));

        if ($res && $res['response']) {
            $res['response'] = json_decode($res['response'], true);

            // if (app()->singleton('debugbar')->hasCollector('service')) {
            //     $array = [
            //         0 => $cmd,
            //         1 => $res['calltime'],
            //         2 => $res['response']
            //     ];
            //     app()->singleton('debugbar')->getCollector('service')->setData($array);
            // }

            yield $res['response'];
        }

        yield false;
    }

    public function addCall($cmd, $data = [])
    {   
        $callId = $this->callId;
        $this->calls['cmd'][$callId] = $cmd;
        $this->calls['data'][$callId] = $data;
        $this->callId++;

        return $callId;
    }

    public function multiCall($timeout = false)
    {   
        $res = (yield $this->call($this->calls['cmd'], $this->calls['data'], $timeout));
        $this->callId = 0;
        $this->calls = [];
        yield $res;
    }
}
