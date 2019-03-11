<?php

namespace app\controller;

use pms\Controller\WsBase;
use pms\Output;

/**
 * 建立连接
 * Class Open
 * @package app\controller
 */
class Open extends WsBase
{

    /**
     * 默认方法
     */
    public function index()
    {
        Output::output($this->counnect->getFd(), '20');
        $this->counnect->send([
            "成功!"
        ]);
    }

    public function index2()
    {
        Output::output($this->counnect->getFd(), '29');
        Output::output($this->proxyCS, '30');
        $this->counnect->send([
            "index2".uniqid()
        ]);
    }
}