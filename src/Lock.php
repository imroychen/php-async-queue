<?php

namespace iry\queue;

class Lock
{

    private $_callback;
    private $_key;

    public function __construct($key)
    {
        $this->_key = $key;
        if(function_exists('sem_get')){
            $this->_hasSem = true;
        }
    }

    /**
     * 自旋等待
     * @param callable $callback
     * @return false|mixed
     */
    public function wait($callback){
        if(is_callable($callback)) {
            if($this->_hasSem) {
                return $this->_lockByFile($callback);
            }else{
                return $this->_lockBySem($callback);
            }
        }
        else{
            echo "$callback Error";
            return false;
        }
    }

    private function _lockBySem($callback){
        $key = ftok('/tmp', 'a');
        $id = sem_get($key);
        while (1) {
            if (sem_acquire($id)) {
                echo "读取任务>";
                if (is_callable($callback)) {
                    $r = call_user_func($callback, $this->_key);
                } else {
                    $r = false;
                    echo "$callback Error";
                }
                sem_release($id);
                return $r;
            }
        }

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
                $r = call_user_func($callback, $this->_key);

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