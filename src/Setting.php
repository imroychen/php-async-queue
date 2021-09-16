<?php

namespace iry\queue;

interface Setting
{
    /**
     * 处理队列的方法 （服务端使用）
     * @return bool 成功队列将会被移除
     */
    function processMsg($taskId,$taskName,$taskArgs,$taskTags);

    /**
     * ClassName?参数
     *  存储器使用方法
     * 格式：className?http_query_string
     * 1. 自定义Mysql存储器 \MyNamespace\MyStorage?table=my_table_name'
     * 2. 自定义Redis存储器 \MyNamespace\MyStorageRedis?key=my_data_key'
     * 3. 内置的Redis存储器  @Redis?host=localhost&port=6379&key=my_data_key&password=123
     * 注：
     * 1和 2可以使用上下文的 DB Link 或者 Redis 实例.
     * 3.会重新实例一次
     * @example ../example/SettingTest.php
     *
     -- mysql作为存储需要用到的表结果
     CREATE TABLE `my_table_name` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `q_sign` char(32) CHARACTER SET latin1 NOT NULL,
        `q_name` varchar(100) CHARACTER SET latin1 NOT NULL,
        `q_args` text CHARACTER SET latin1 NOT NULL COMMENT '//{}',
        `q_exec_time` int(11) unsigned NOT NULL,
        PRIMARY KEY (`id`),
        KEY `q_exec_time` (`q_exec_time`),
        KEY `q_sign` (`q_sign`) USING BTREE
     ) ENGINE=MyISAM AUTO_INCREMENT=135 DEFAULT CHARSET=utf8;
     *
     * @return string
     */

    function storage();


    /**
     * @param string $name 任务名称
     * @param Client $client 对象
     * @return bool false 阻止创建
     */

    function beforeCreate($name,$client);

    /**
     * @param string $id
     */
    function afterCreate($id);
}