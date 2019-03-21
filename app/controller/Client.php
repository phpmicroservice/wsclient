<?php

namespace app\controller;

use pms\Controller\WsBase;
use pms\Output;

/**
 * 关闭链接
 * Class Close
 * @package app\controller
 */
class Client extends WsBase
{

    /**
     * 默认方法,链接方法
     * @param [\pms\bear\WsCounnect] $params
     */
    public function index(array $params)
    {
        //counnect
        Output::output($params[0]->getFd(), 'Close');
        Output::output($params[0]->getSid(), 'Close');

    }

    public function index2()
    {

    }
}