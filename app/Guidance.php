<?php

namespace app;

use function Couchbase\fastlzCompress;
use Phalcon\Events\Event;
use pms\App;
use pms\Output;

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
    public function beforeStart(Event $event, \pms\Server $pms_server, \Swoole\Server $server)
    {
        output('beforeStart  beforeStart', 'beforeStart');
        $server->channel = new \Swoole\Channel(1024 * 1024 * 128);# 128M

    }

    /**
     * 启动事件
     * @param Event $event
     * @param \pms\Server $pms_server
     * @param \Swoole\Server $server
     */
    public function onWorkerStart(Event $event, \pms\Server $pms_server, \Swoole\Server $server)
    {
        output($server->taskworker, 'guidance');
        # 绑定一个权限验证
        $this->eventsManager->attach('Router:handleCall', $this);
        # 绑定一个准备判断和准备成功
        $this->eventsManager->attach('Server:readyJudge', $this);
        $this->eventsManager->attach('Server:readySucceed', $this);
        $this->eventsManager->attach('App:receive', function (Event $event, App $app) {
            \output(get_class($event), '6262262662');
            $app->eventsManager->attach('dispatch:beforeDispatch', function ($Event, \pms\Dispatcher $dispatch) {
                $s = $dispatch->connect->s;
                if (!empty($s)) {
                    $dispatch->setTaskName('fault');
                    $dispatch->setActionName('proxy');
                }
            });
            $app->setEventsManager($app->eventsManager);
        });
    }


    /**
     * 准备判断
     */
    public function readyJudge(Event $event, \pms\Server $pms_server, $timeid)
    {

        $this->dConfig->ready = true;
        output('初始化完成', 'init');
    }

    /**
     * 准备完成
     */
    public function readySucceed(Event $event, \pms\Server $pms_server, \Swoole\Server $swoole_server)
    {
        if ($this->cache->get('UpdateServer94')) {
            $this->cache->save('UpdateServer94', false);
            \output("96969696969666666");
            #实例化更新服务列表
            $ser = new logic\UpdateServer($swoole_server);
            $ser->start();
        }

    }


    public function onStart()
    {
        $this->cache->save('UpdateServer94', true);
    }

    /**
     * 路由事件
     * @param Event $event
     * @param \pms\Router $router
     * @param $data
     */
    public function handleCall(Event $event, \pms\Router $router, $data)
    {
        return true;
    }

}
