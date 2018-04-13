<?php

namespace app\logic;

/**
 * 服务更新的逻辑层
 * @property \pms\bear\Client $register_client
 * @author Dongasai<1514582970@qq.com>
 */
class UpdateServer extends \app\Base
{

    private $client_ip;
    private $client_port;
    private $register_client;

    /**
     * 初始化
     */
    public function __construct($swoole_server)
    {
        $this->client_ip = get_env('REGISTER_ADDRESS', 'pms_register');
        $this->client_port = get_env('REGISTER_PORT', '9502');
        $this->register_client = new \pms\bear\Client($swoole_server, $this->client_ip, $this->client_port, [], 'UpdateServer');
        # 进行回调绑定
        $this->register_client->on($this);
        $UpdateServer = $this;
        swoole_timer_tick(3000, function ($timeid) use ($UpdateServer) {
            output('更新服务列表', 'updateService');
            $UpdateServer->start();
        });
    }

    /**
     * 开始
     */
    public function start()
    {
        if (!$this->register_client->isConnected()) {
            $this->register_client->start();
        } else {
            $this->update();
        }
    }

    /**
     * 更新
     */
    public function update()
    {
        $data = [
            'name' => strtolower(SERVICE_NAME),
            'host' => APP_HOST_IP,
            'port' => APP_HOST_PORT,
            'k' => $this->get_key()
        ];
        $this->register_client->send_ask('service_getall', $data);
    }

    /**
     * 获取通讯key
     * @return string
     */
    private function get_key()
    {
        defined('REGISTER_SECRET_KEY') || exit('缺少必要的常量REGISTER_SECRET_KEY');
        return md5(md5(REGISTER_SECRET_KEY) . md5(strtolower(SERVICE_NAME)));
    }

    /**
     * 接收
     */
    public function receive(\Phalcon\Events\EventInterface $event, \pms\bear\Client $Client, $data)
    {
        //output($data, 'UpdateServer');
        if ($data['e']) {
            # 出错
        } else {
//            output($data, 'US_receive');
            # 正确的
            if ($data['t'] == 'service_getall') {
                # 我们需要的数据
                $list = $data['d'];
                foreach ($list as $name => $value) {
                    $cache_key = '74_' . $name;
                    $this->gCache->save($cache_key, $value, 20);
                }
            }
        }
    }

}
