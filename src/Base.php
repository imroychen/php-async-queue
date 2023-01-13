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

    private $_langPackage;

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

        $this->_initLangPackage();
    }

    private function _initLangPackage(){
        $i18nCfg = trim($this->_setting->i18n());
        if(strpos($i18nCfg,'file://')===0){
            $i18nFile = str_replace('file://','',$i18nCfg);
            $i18n = is_file($i18nFile)? include($i18nFile):[];
        }
        elseif (is_array($i18nCfg)){
            $i18n = $i18nCfg;
        }
        else {
            $i18nFile = __DIR__ . '/i18n/' . $this->_setting->i18n() . '.php';
            $i18n = is_file($i18nFile)? include($i18nFile):[];
        }
        $this->_langPackage = array_merge(include(__DIR__.'/i18n/en-US.php'),$i18n);
    }

    protected function _t($key,$args){
        if(isset($this->_langPackage[$key])){
            $r = $this->_langPackage[$key];
            if(!empty($args)) {
                foreach ($args as $k => $v) {
                    $r = str_replace('{%' . $k . '}', $v, $r);
                }
            }
            return $r;
        }
        else{
            return $key;
        }
    }

}