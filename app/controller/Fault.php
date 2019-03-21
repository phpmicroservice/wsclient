<?php

namespace app\controller;

use app\logic\Proxy;
use pms\bear\WsCounnect;
use pms\Controller\WsBase;
use pms\Session;
use Swoole\WebSocket\Server;


/**
 * 需要转发的请求
 */
class Fault extends WsBase
{

    /**
     * 不合法的控制器名字
     * @param $data
     */
    public function proxy($per)
    {
        $this->di->getShared('proxyClient');
        \pms\output(__FUNCTION__, 'Fault_proxy');
        $this->run2($per[0],$per[1]);
    }

    public function run2(WsCounnect $counnect,Server $server){
        $data2 = $this->call_service($counnect,$server);
        if($data2){
            $this->proxy_send($data2,$counnect);
        }
    }

    /**
     * 服务的处理
     */
    public function call_service(WsCounnect $counnect,Server $server)
    {
        $sid = $counnect->getSid();
        $session=new Session($sid);
        $session->set('fd',$counnect->getFd());
        $session_id = $session->get('session_id');
        if(is_null($session_id)){
            $counnect->send([
                428,'请先获取或设置SID'
            ]);
            return false;
        }
        $data =$counnect->getData();
        $router=new \Phalcon\Mvc\Router();

        // Define a route
        $router->add(
            '/:module/:controller/:action',
            [
                'module'=>1,
                'controller' => 2,
                'action'     => 3
            ]
        );
        $router_string=$counnect->getRouterString();
        $router->handle($router_string);
        $data2 = [
            's' => $router->getModuleName(),
            'r' => '/' . $router->getControllerName() . '/' . $router->getActionName(),
            'd' => $data['d'],
            'p' => $sid,
            'sid' => $session_id,
            'f' => strtolower(SERVICE_NAME)
        ];
        return $data2;
    }

    /**
     * 代理发送
     * @param $data
     * @param $fd
     */
    public function proxy_send($data, WsCounnect $counnect)
    {

        $proxy = $this->di->getShared('proxyClient');

        \pms\output([get_class($proxy), $data], 'proxy_send');
        if ($proxy instanceof \pms\bear\Client) {
            if (!$proxy->isConnected()) {
                $proxy->start();
            }
            $re = $proxy->send($data);
            if ($re === false) {
                $counnect->send([500, "服务异常!,请稍后重试或联系管理员!"]);
            }
        }

    }

    /**
     * 不合法的处理器名字
     */
    public function action()
    {

    }


}