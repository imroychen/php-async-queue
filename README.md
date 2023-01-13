中文简体  / [English](README-EN.md)
# 自述
A simple asynchronous queue library.(一个简单php异步队列库)
## 安装
composer require iry/queue

## 使用

### 1. 队列配置
> 创建一个class 实现以下方法即可。[示例](example/QueueConfig/SettingTest.php)
一个项目中可以有多个队列，请为每个队列创建一个配置(Setting)

接口文档 [src/SettingInterface.php](src/SettingInterface.php)

```php
namespace MyNamespace;//这您的项目应用的命名空间
//创建一个队列配置（Setting））
class MySetting extends \iry\queue\Setting{
    function storage(); // 返回异步任务存储驱动
    function tempPath(){}
    
    //[可选]
    //function beforeCreate($name, $client);// 任务入队前回调, return false 阻止任务继续入队
    
    //[可选]
    //function afterCreate($id);//任务成功入队后回调
    
    //[可选]
    //function i18n(){} //默认 en-US 系统自带 en-US，zh-CN or file://你的绝对路径
}
```

### 2.新任务入队/创建新任务 (客户端)
用于产生队列任务
```php 
use iry\queue\Client
Client::m(\MyNamespace\MySetting::class)->create($name,$args,$tags ,$execTime)
```
**create**(_$name, $args, $customID ,$tags ,$execTime, $unique=true_) [代码示例](./example/CreateTask.php)

参数名|类型|说明
---|---|---
$name|string|任务名称：（字母 数组 _）等字符组成
$args|array|参数，如:['id'=>123]
$customID|string|自定义ID （便于管理和定位您的任务）
$execTime|array|执行时间 用来为队列延时使用的。

### 3. 监听并处理步任务，仅仅支持CLI模式 (服务端)
请在你的应用的CLI模式下执行
```php 
use iry\queue\Service

new Service()->listen()
```
---
# 注：
## MySetting::storage
[详情](./src/Setting.php)

[示例](./example/Queue2Config/MyDbStorage.php)
