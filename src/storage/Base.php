<?php

namespace iry\queue\storage;
use \iry\queue as lib;
// todo 请使用数据库（单服务器小内存推荐）或者 Redis（大内存或者有独立的Redis服务器推荐）
abstract class Base
{
    function __construct($rawArgs){
        $_argsArr = explode('&',$rawArgs);
        $args = [];
        if(count($_argsArr)>0) {
            foreach ($_argsArr as $item) {
                $tmp = explode('=', $item . '=');
                $args[trim($tmp[0])] = urldecode(trim($tmp[1]));
            }
        }
        return $this->_init($args,$rawArgs);
    }

    abstract protected function _init($args,$rawArgs);

    protected function _getVersion(){
        return lib\Base::VERSION;
    }

    protected function _decode($data){
        return unserialize($data);
    }
    protected function _encode($data){
        return serialize($data);
    }
}