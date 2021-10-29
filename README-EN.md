[中文简体](README.md)  /  English
# Readme
A simple asynchronous queue library.
## Installation
composer require iry/queue

## How to use

### 1. Queue configuration
Create a class to implement the following methods。([Example](example/QueueConfig/SettingTest.php))
There can be multiple queues in a project, please create a configuration for each queue (Setting)
```php
namespace MyNamespace;//This is the namespace of your project
//
class MySetting implements \iry\queue\Setting{
    //This method is used by the server to process asynchronous tasks
    function processMsg($taskId, $taskName, $taskArgs,$taskTags);
    
    // return an asynchronous task storage driver
    function storage(); 
    //Call back before the task joins the team, return false to prevent the task from continuing to join the team
    function beforeCreate($name, $client);
    //Callback after the task is successfully enqueued
    function afterCreate($id);
    function tempPath(){}
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
