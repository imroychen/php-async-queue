<?php

namespace iry\queue;

class Base
{
    const VERSION = 1.0;
    protected $_signalFile;
    /**
     * @var storage\Redis | storage\Db
     */
    protected $_driver;
    /**
     * @var Setting
     */
    protected $_setting;

    private function signalFile($name=''){
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.'signal-async-queue'.$name;
    }

    /**
     * @param string|Setting $settingCls
     */

    public function __construct($settingCls)
    {

        if(is_string($settingCls)) {
            $this->_setting = new $settingCls();
        }else{
            $this->_setting = $settingCls;
        }
        $this->_signalFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'async-queue-signal'.substr(md5(get_class($this->_setting)),8,10);

        $driver = $this->_setting->storage();
        list($storageCls,$storeageArgs)=explode('?',$driver.'?');
        $storageCls = str_replace('@',__NAMESPACE__.'\\storage\\',$storageCls);
        $this->_driver = new $storageCls($storeageArgs);
    }
}