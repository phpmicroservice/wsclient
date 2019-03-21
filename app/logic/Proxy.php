<?php

namespace app\logic;

use app\Base;

/**
 * Class Proxy
 * @property \Swoole\Server $swoole_server
 * @package app\logic
 *
 */
class Proxy extends Base
{
    private static $instance;
    public $connectend = false;
    public $swoole_server;
    public $server_name;
    private $proxy_client;
    private $client_ip;
    private $client_port;

    private function __construct($server, $client_ip, $port)
    {
        $this->client_ip = $client_ip;
        $this->client_port = $port;
        $this->swoole_server = $server;
        $this->proxy_client = new \pms\bear\Client($server, $this->client_ip, $this->client_port);
        $this->proxy_client->onBind('receive', $this);
        $this->proxy_client->onBind('connect', $this);
        $this->proxy_client->onBind('error', $this);
        $this->proxy_client->onBind('close', $this);
        $this->lianjie();
    }

    /**
     * 链接
     */
    public function lianjie()
    {
        if (!$this->proxy_client->isConnected()) {
            $this->proxy_client->start();
        }


    }

    /**
     * 获取单例对象 并且链接一下
     * @param $server
     * @return Proxy
     */
    static public function getInstance($server, $server_name)
    {
        //判断$instance是否是Uni的对象
        //没有则创建
        if (!self::$instance[$server_name] instanceof self) {
            self::start($server, $server_name);
        }

        return self::$instance[$server_name];
    }

    /**
     * 获取链接信息
     * Server constructor.
     * @param $server
     * @param $server_name
     */
    private static function start($server, $server_name)
    {
        $cache_key = '74_' . $server_name;
        $gCache = \Phalcon\Di::getDefault()->getShared('gCache');
        $config_list = $gCache->get($cache_key, []);

        if (count((array)$config_list) > 1) {
            $config = $config_list[mt_rand(0, count($config_list) - 1)];
        } else {
            $config = $config_list[0];
        }
        if (empty($config)) {
            self::$instance[$server_name] = "服务不存在!" . $server_name;
            return "服务不存在!" . $server_name;
        }
        \pms\output($config, '服务链接配置' . $server_name);
        self::$instance[$server_name] = new self($server, $config['host'], $config['port']);
        self::$instance[$server_name]->server_name = $server_name;

    }

    /**
     * 发送
     * @param $data
     * @param $fd
     * @return bool|void
     */
    public function send($data, $fd)
    {
        if (isset($data['p'])) {
            $p = [
                $data['p'],
                $fd
            ];
        } else {
            $p = [
                '',
                $fd
            ];
        }
        $data['p'] = $p;
        if ($this->proxy_client->isConnected) {
            return $this->proxy_client->send($data);
        }
        self::start($this->swoole_server, $this->server_name);
        return false;

    }

    /**
     * 链接成功的回调
     */
    public function connect()
    {
        \pms\output('代理器链接成功');
        $this->connectend = true;

    }


    /**
     * 错误的回调
     */
    public function error()
    {
        $this->connectend = false;
        $this->proxy_client->swoole_client->close();
        \pms\output('出错', '代理器');
        # 自动重连
        //self::start($this->swoole_server, $this->server_name);
    }

    /**
     * 关闭的回调
     */
    public function close()
    {
        \pms\output('关闭', '代理器');
        $this->connectend = false;
        self::$instance[$this->server_name] = null;
        # 自动重连
        //self::start($this->swoole_server, $this->server_name);
    }

    /**
     *  接收返回值
     */
    public function receive(\Phalcon\Events\EventInterface $event, \pms\bear\Client $client, $data)
    {
        \pms\output($data, '代理器收到消息');
        $this->connectend = true;
        $fd = $data['p'][1];
        $data['p'] = $data['p'][0];
        $data['mt'] = strtolower($data['f']) . '@' . $data['t'];
        \pms\output($data, '代理器要返回的');
        $this->swoole_server->send($fd, $this->encode($data));

    }


    /**
     * 编码
     * @param array $data
     * @return string
     */
    private function encode(array $data): string
    {
        $msg_normal = \pms\Serialize::pack($data);
        $msg_length = pack("N", strlen($msg_normal)) . $msg_normal;
        return $msg_length;
    }


    public function __destruct()
    {
        \pms\output('销毁代理器对象');
    }

}