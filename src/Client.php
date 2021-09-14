<?php

namespace iry\queue;


class Client extends Base
{
    static private $_container = [];

    /**
     * main
     */

    static public function m($settingCls){
        if(isset(self::$_container[$settingCls])) return self::$_container[$settingCls];

        if (is_object($settingCls)) {
            $clsName = get_class($settingCls);
        } else {
            $clsName = $settingCls;
        }

        if(!isset(self::$_container[$clsName])) {
            self::$_container[$clsName] = new static($settingCls);
        }
        return self::$_container[$clsName];
    }

    /**
     * @param string $name
     * @param array $args
     * @param int $delay 延时的秒数/Delay in seconds
     * @param bool $uniq
     */

    function delayedExec($name,$args,$tags=[],$delay=1,$uniq=true){
        $this->create($name,$args,$tags,time()+$delay,$uniq);
    }

    /**
     * @param string $name
     * @param array $args
     * @param int $execTime 时间戳/Timestamp
     * @param bool $unique 是否去重
     * @return bool|int|string
     */
    function create($name,$args,$tags=[],$execTime=0,$unique=true){

        $data = [
            'q_name'=>$name,
            'q_args'=>$args,
            'q_exec_time'=>$execTime,
            'q_tags'=>is_array($tags)?implode(',',$tags):$tags
        ];

        if(is_callable([$this->_setting,'beforeCreate']) && call_user_func([$this->_setting,'beforeCreate'],$name,$this)) {
            $queueId = $this->_driver->create($data,$unique);
            file_put_contents($this->_signalFile, uniqid());
            if(is_callable([$this->_setting,'afterCreate'])){
                call_user_func([$this->_setting,'afterCreate'],$queueId);
            }
            return $queueId;
        }
        return false;
    }
}