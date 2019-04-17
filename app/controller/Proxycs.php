<?php

namespace app\controller;

use pms\bear\ClientCounnect;
use pms\Output;
use pms\Session;
use Swoole\Server;

class Proxycs extends \Phalcon\Di\Injectable
{
    public function connect()
    {
        Output::output('connect', 'Proxycs');
    }

    public function receive(array $params)
    {
        //counnect
        if ($params[0] instanceof ClientCounnect) {
            $ClientCounnect = $params[0];

            $data = $ClientCounnect->getData();
            $sid = $data['p'];
            $Session=new Session($sid);
            $fd=$Session->get('fd');
            Output::output($data, 'Proxycs');
            if(is_null($fd)){
                Output::output('客户端已经关闭!', 'Proxycs');
                return $fd;
            }
            if ($data['e'] ?? 0) {
                //出错!
                $data2 = [
                    'sc' => $data['sc'] ?? 400,
                    'e'  => 1,
                    'm'  => $data['m'] ?? '',
                    'd'  => $data['d'] ?? null,
                    'st' => $data['st'],
                    'ts' => time()
                ];
            } else {

                //没有错误
                $data2 = [
                    'sc' => 200,
                    'e'  => 0,
                    'm'  => $data['m'] ?? '',
                    'd'  => $data['d'] ?? null,
                    'st' => $data['st'],
                    'ts' => time()
                ];
            }
            $server=\Phalcon\Di\FactoryDefault\Cli::getDefault()->getShared('server');
            Output::output(get_class($server), 'server');
            if ($server instanceof \Swoole\WebSocket\Server) {
                $server->push($fd,$this->encode($data2));

            }


        }


    }
    /**
     * 编码
     * @param array $data
     * @return string
     */
    private function encode($data): string
    {
        $msg_normal = \pms\Serialize::pack($data);
        return $msg_normal;
    }

    public function error()
    {
        Output::output('error', 'Proxycs');
    }

    public function close()
    {
        Output::output('close', 'Proxycs');
    }

    public function bufferFull()
    {

    }

    public function bufferEmpty()
    {

    }

}