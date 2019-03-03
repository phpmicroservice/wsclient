<?php

namespace app;


use Phalcon\Events\Event;


/**
 * 引导类,初始化
 * Class guidance
 * @property \app\table\server $server_table
 * @property \Phalcon\Cache\BackendInterface $cache
 *
 * @package app
 */
class Guidance extends \Phalcon\Di\Injectable
{

    /**
     * 构造函数
     * guidance constructor.
     */
    public function __construct()
    {

    }

    /**
     * 开始之前
     * @param Event $event
     * @param \pms\Server $pms_server
     * @param \Swoole\Server $server
     */
    public function beforeStart(Event $event, \pms\Base $pms_server, \Swoole\Server $server)
    {

    }

    public function onStart(Event $event, \pms\Base $pms_server, \Swoole\Server $server)
    {
        \pms\Output::output("onStart in Guidance");
    }






}
