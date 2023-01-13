<?php

namespace iry\queue;

interface SettingInterface
{
    /**
     * [中文说明]
     * 存储器使用方法
     * 格式：className?http_query_string
     * 1. 自定义Mysql存储器 \MyNamespace\MyStorage?table=my_table_name'
     * 2. 自定义Redis存储器 \MyNamespace\MyStorageRedis?key=my_data_key'
     * 3. 内置的Redis存储器 @Redis?host=localhost&port=6379&key=my_data_key&password=123
     * 注：
     * 1和 2可以使用您项目中上下文的 DB Link 或者 Redis 实例.
     * 3.会重新实例并连接一次
     *
     * [中文说明]
     * Format：className?http_query_string
     * 1. Custom Mysql storage: \MyNamespace\MyStorage?table=my_table_name'
     * 2. Custom Redis storage: \MyNamespace\MyStorageRedis?key=my_data_key'
     * 3. Built-in Redis storage: @Redis?host=localhost&port=6379&key=my_data_key&password=123
     *
     * @return string
     * @example ../example/SettingTest.php
     *
     */

    function storage();

    /**
     * [中文说明]
     * 提供一个可写的目录临时目录（确保Web环境和Cli环境都能正常读写）
     * 用于缓冲临时数据
     *
     * [EN description]
     * Provide a writable directory temporary directory (to ensure that both the Web environment and the Cli environment can read and write normally)
     * This directory is used to buffer temporary data
     *
     * @return string
     */
    function tempPath();

    /**
     * @return string|array
     *
     * -------------------------------
     *
     * [EN description]
     * Built-in language support: "zh-CN","en-US"
     * @example return 'zh-CN'
     *
     * Load custom i18n file (file contents reference: i18n/en-US.php)
     * @example return 'file://Custom file address'
     * @example return 'file:///mnt/web/lang/zh-Cn.php'
     *
     * Load custom i18n Contents. (content reference: i18n/en-US.php)
     * @example return []
     *
     * ........................
     *
     * [中文说明]
     * 内置的语言包支持：'zh-CN,en-US'
     * @example return 'zh-CN'
     *
     * 装载自定义的 i18n 文件 (文件的内容参考: i18n/en-US.php)
     * @example return 'file://文件绝对路径'
     * @example return 'file:///mnt/web/lang/zh-Cn.php'
     *
     * 装载自定义的 i18n 内容。 (内容参考: i18n/en-US.php)
     * @example return []
     */
    function i18n();


    /**
     * [EN]
     * Actions before entering the team
     * It is convenient for you to make some callbacks of events, messages, hooks, etc. in business projects
     *
     * [ZH]
     * 入队前的动作
     * 便于您在业务项目中做一些事件、消息、钩子等的回调
     *
     * @param string $name 任务名称
     * @param Client $client 对象
     * @return bool false 阻止创建
     */

    function beforeCreate($name, $client);

    /**
     * [ZH]
     * 入队成功后动动作
     * 便于您在业务项目中做一些事件、消息、钩子等的回调
     *
     * [EN]
     * Action after joining the team successfully
     * It is convenient for you to make some callbacks of events, messages, hooks, etc. in business projects
     * @param string $id
     */
    function afterCreate($id);
}