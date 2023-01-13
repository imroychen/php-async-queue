<?php

namespace iry\queue;

abstract class Setting implements SettingInterface
{

    /**
     * @inheritDoc
     * @return string
     */

    abstract function storage();

    /**
     * @inheritDoc
     * @return string
     */

    abstract function tempPath();

    /**
     * @inheritDoc
     */
    public function i18n(){
        return 'en-US';
    }


    /**
     * @param string $name 任务名称
     * @param Client $client 对象
     * @return bool false 阻止创建
     */

    function beforeCreate($name,$client){
        return true;
    }

    /**
     * @param string $id
     */
    function afterCreate($id){

    }

    /**
     * 为了版本通用 ::cls() 代替 ::CLASS
     * 5.6之前不支持::CLASS
     * @return string
     */
    static function cls(){
        return get_called_class();
    }
}