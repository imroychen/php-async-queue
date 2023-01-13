[中文简体](README.md)  /  English
# Readme
A simple asynchronous queue library.
## Installation
composer require iry/queue

## How to use

### 1. Queue configuration
>Create a class to implement the following methods。([Example](example/QueueConfig/SettingTest.php))
There can be multiple queues in a project, please create a configuration for each queue (Setting)
> 
> Interface: [src/SettingInterface.php](src/SettingInterface.php)


```php
namespace MyNamespace;//This is the namespace of your project
//
class MySetting extends \iry\queue\Setting{
    function storage();
    function tempPath(){}
    
    //function beforeCreate($name, $client);// 任务入队前回调, return false 阻止任务继续入队
    
    //function afterCreate($id);
    
    //function i18n(){ return 'en-US'; } // en-US，zh-CN or file://.
}
```

### 2.Create new task (client)
```php 
use iry\queue\Client
Client::m(\MyNamespace\MySetting::class)->create($name,$args,$tags ,$execTime)
```
**create**(_$name, $args, $tags ,$execTime, $unique=true_) ([Example](./example/CreateTask.php))

Parameter name|Type|Description
---|---|---
$name|string|Task name: (letters/numbers/ _) and other characters
$args|array|arguments .E.G:['id'=>123]
$tags|array|
$execTime|array|The execution time is used to delay the queue.

### 3. Monitor and execute asynchronous tasks, only support CLI mode (server side)
```php 
use iry\queue\Service

new Service()->listen()
```
---
# 注：
## MySetting::storage
[Info](./src/Setting.php)

[E.g.](./example/Queue2Config/MyDbStorage.php)
