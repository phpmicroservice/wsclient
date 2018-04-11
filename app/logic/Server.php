<?php

namespace app\logic;

use app\Base;

/**
 * 服务链接托盘
 * Class Server
 * @package app\logic
 */
class Server extends Base
{
    private static $instance;


    /**
     * 获取单例对象 并且链接一下
     * @param $server
     * @return Proxy
     */
    static public function getInstance($server, $server_name)
    {
        //判断$instance是否是Uni的对象
        //没有则创建
        if (!self::$instance[$server_name] instanceof Proxy) {
            new self($server, $server_name);
        }

        return self::$instance[$server_name];
    }
}