<?php

namespace MyNamespace\QueueConfig;

class SettingTest implements \iry\queue\Setting
{

    /**
     * @param $taskId
     * @param $taskName
     * @param $taskArgs
     * @param $taskTags
     * @return bool
     */
    //异步服务调用
    function processMsg($taskId, $taskName, $taskArgs,$taskTags)
    {
        // TODO: 异步处理任务在这里执行
        // 建议一个$taskName种类使用一个class单独处理
        return true;
    }


    //返回异步任务存储驱动
    function storage()
    {
        return '@Redis?host=localhost&port=6379&password=my-password&key=my_queue_data_key';

    }

    function tempDir(){
        return sys_get_temp_dir();
    }

    /**
     * 任务入队前回调
     * @return bool
     */
    function beforeCreate($name, $client)
    {
        // TODO: 创建任务之前的钩子
        // return false 阻止任务继续入队

        return true;
    }

    /**
     * 任务成功入队后回调
     */
    function afterCreate($id)
    {
        // TODO: 任务成功入队后 钩子
    }
}