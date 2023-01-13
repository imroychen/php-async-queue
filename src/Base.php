<?php

namespace iry\queue;

class Base
{
    const VERSION = 2.0;
    protected $_signalFile;
    /**
     * @var storage\Redis | storage\Db
     */
    protected $_driver;
    /**
     * @var Setting
     */
    protected $_setting;

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
        $this->_signalFile = $this->_setting->tempPath().DIRECTORY_SEPARATOR.'async-queue-signal'.substr(md5(get_class($this->_setting)),8,10);

        $driver = $this->_setting->storage();
        list($storageCls,$storageArgs)=explode('?',$driver.'?');
        $storageCls = str_replace('@',__NAMESPACE__.'\\storage\\',$storageCls);
        $this->_driver = new $storageCls($storageArgs);
    }
}