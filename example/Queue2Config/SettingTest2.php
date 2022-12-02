<?php

namespace MyNamespace\Queue2Config;

class SettingTest2 implements \iry\queue\Setting
{

    /**
     * @param $taskId
     * @param $taskName
     * @param $taskArgs
     * @return bool
     */
    //异步服务调用
    function processMsg($taskId, $taskName, $taskArgs,$tags)
    {
        // TODO: 异步处理任务在这里执行
        // 建议一个$taskName种类使用一个class单独处理
        return true;
    }



    function storage()
    {
        // 返回异步任务存储驱动
        $path = __DIR__;
        //sqlite
        return __NAMESPACE__."\\MyDbStorage?table=async_queue&dsn=sqlite:$path/sqlite.db";
        //mysql
        return __NAMESPACE__.'\\MyDbStorage?table=async_queue&dsn=' .urlencode('mysql:host=localhost;dbname=test'). '&username=test&password=123';

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

    function tempPath()
    {
        return __DIR__;
    }
}