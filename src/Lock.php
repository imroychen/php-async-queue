<?php

namespace iry\queue;

class Lock
{

    private $_callback;
    private $_key;

    public function __construct($key)
    {
        $this->_key = $key;
    }

    /**
     * 自旋等待
     * @param callable $callback
     * @return false|mixed
     */
    public function wait($callback){
        return $this->_lockByFile($callback);
    }

    private function _lockByFile($callback){
        $lockFile = $this->_key.'.lock';
        while(1) {
            $fp = fopen($lockFile, "w+");
            if(!$fp && !is_file($lockFile)){
                file_put_contents($lockFile,'');
                $fp = fopen($lockFile, "w+");
            }

            if (flock($fp, LOCK_EX)) {
                if(is_callable($callback)){
                    $r = call_user_func($callback, $this->_key);
                }else{
                    $r = false;
                    echo "$callback Error";
                }

                flock($fp, LOCK_UN);
                fclose($fp);
                return $r;
            }
            else {
                fclose($fp);
                usleep(1000*50);
            }
        }
    }
}