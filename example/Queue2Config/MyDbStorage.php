<?php

namespace MyNamespace\Queue2Config;

use iry\queue\storage\Db;
use iry\queue\Store\Redis;

//e.g. 1  示例 1
//'\MyNamespace\MyDbStorage?dsn=my-table-name';
class MyDbStorage extends Db
{
    private $_db;
    protected function _init($args, $rawArgs)
    {
        parent::_init($args, $rawArgs);
        $username = isset($args["username"])?$args["username"]:null;
        $password = isset($args["password"])?$args["password"]:null;

        $this->_db = new \PDO($args['dsn'],$username,$password);
    }

    /**
     * @param string $sql
     * @return array
     */
    protected function _query($sql)
    {
        //query sql
        $sth = $this->_db->prepare($sql);
        if($sth) {
            $sth->execute();
            $_result = $sth->fetchAll(\PDO::FETCH_CLASS);

            if(!empty($_result)){
                foreach ($_result as &$v) {
                    if (is_object($v)) {
                        $v = get_object_vars($v);
                    }
                }unset($v);
            }
            return $_result;
        }
        return [];
    }

    /**
     * @param string $sql
     * @param string $sqlType
     * @return bool
     */

    protected function _exec($sql,$sqlType){
        return $this->_db->exec($sql);
    }
}