<?php

namespace app\controller;

use pms\bear\WsCounnect;
use pms\Controller\WsBase;
use pms\Output;
use pms\Session;

/**
 * 建立连接
 * Class Open
 * @package app\controller
 */
class Open extends WsBase
{

    /**
     * 默认方法,链接方法
     * @param [\pms\bear\WsCounnect] $params
     */
    public function index(array $params)
    {
        //counnect
        Output::output($params[0]->getFd(), 'index2');
        Output::output($params[0]->getSid(), 'index3');

    }

    /**
     * 获取sid
     * @param array $params
     */
    public function getsid(array $params)
    {
        $counnect = $params[0];
        $sid = $counnect->getSid();
        $session=new Session($sid);
        $session_id = $session->get('session_id');
        if(is_null($session_id)){
            $session_id =uniqid().'_'.md5(uniqid().time().$counnect->getFd());
            $session->set('session_id',$session_id);
        }
        $counnect->send([
            200,"获取成功!",['sid'=>$session_id]
        ]);
    }


    /**
     * 设置SID
     * @TODO 这个设置操作应该进行验证,请自行书写逻辑
     * @param array $params
     */
    public function setsid(array $params)
    {
        $counnect = $params[0];
        if($counnect instanceof WsCounnect){
            $sid = $counnect->getSid();
            $session=new Session($sid);
            $session_id = $session->get('session_id');
            if(is_null($session_id)){
                $session_id =$counnect->getContent();
                $session->set('session_id',$session_id);
                $counnect->send([
                    200,"设置成功!"
                ]);
            }else{
                $counnect->send([
                    406,"SID已经设置"
                ]);
            }

        }

    }


}