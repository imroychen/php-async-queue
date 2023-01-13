# 1.*>2.0
### 环境要求
> 2.0版本要求PHP >=5.6
### 升级方法
1. 升级到 1.n 的最高版本
2. 修改Client->create() 修改： 2.0增加$customID参数
``` php
    //1.n 的参数
    create($name,$args,$tags,$execTime,$uniqueCtrl);
    //2.0的 参数
    // $customID 是新增的参数
    create($name,$args,<$customID>,$tags,$execTime,$uniqueCtrl);
```
3. 修改 队列的配置class (如MyQueueSetting)
   1. 接口由原来的Setting变成SettingInterface
   ```php 
     class MyQueueSetting  implements Setting{}
     class MyQueueSetting  extend Setting{}
   ```
   2. 废除 MyQueueSetting 中的processMsg方法
   ```php
   //改为
   $Service->listen(function($taskId,$name,$customID,$tags,$meta){ ProcessMsg });
   ```



