<?php
require('../no-composer-require.php');
require('autoload-mynamespace.php');



use iry\queue\Client;
use MyNamespace\Queue2Config\SettingTest2;

//--------------------

$client = new Client(SettingTest2::class);

$client->create('test_name',['test_arg_1'=>'1','test_arg_2'=>'2']);//创建异步任务

$client->create('test_name',['test_arg_1'=>'1','test_arg_2'=>'2'],time()+3600);//创建一个延时1小时的异步任务

$client->create('test_name',['test_arg_1'=>'1','test_arg_2'=>'2'],time()+3600,false);//关闭重复检查（允许重复添加）



//--------------------


//m[main的简写]会自动缓存示例 可以轻松实现 单例模式调用
//该方法 会自动执行 “new Client” 并且自动缓存实例

$client = Client::m(SettingTest2::class);
//下面两个方法等效
$client->create('test_name',['test_arg_1'=>'1','test_arg_2'=>'2'],time()+3600);

$client->delayedExec('test_name',['test_arg_1'=>'1','test_arg_2'=>'2'],3600);