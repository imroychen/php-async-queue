<?php

namespace iry\e;

class ProcessComm
{

    /**
     * @var false|resource
     */
    private $_shm;

    public function __construct($path=null,$size=1024,$model=0666)
    {
        $path = empty($path)?__DIR__:$path;
        $_key = ftok($path, 'a');
        $this->_shm = shm_attach($_key, $size, $model); // resource type
    }

    public function set($key,$val){
        if($this->_shm) {
            shm_put_var($this->_shm, $key, $val);
            return true;
        }
        return false;
    }

    public function get($key){
        if($this->_shm) {
            return shm_get_var($this->_shm, $key);
        }
        return null;
    }

    public function rm(){
        if($this->_shm) {
            shm_remove($this->_shm);
        }
        return true;
    }

    public function close(){
        if($this->_shm) {
            shm_detach($this->_shm);
        }
    }

    public function __destruct(){
        $this->close();
    }

    /*function wait($datakey){
        $data = $this->get($datakey);
    }*/
}