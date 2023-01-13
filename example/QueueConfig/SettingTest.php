<?php

namespace MyNamespace\QueueConfig;

class SettingTest extends \iry\queue\Setting
{


    //返回异步任务存储驱动
    function storage()
    {
        return '@Redis?host=localhost&port=6379&password=my-password&key=my_queue_data_key';

    }

    function tempPath(){
        return sys_get_temp_dir();
    }

}