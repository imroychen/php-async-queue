<?php
/**
 * @author Roy
 * 2016-03-06 23:32:06
 */
namespace iry\queue;

class Service extends Base
{
    private $_msgInfo;

    /**
     * @param mixed $taskId
     * @param int $seconds 秒数
     */
    public function delayTask($taskId,$seconds){
        $this->_driver->setExecTime($taskId,time()+$seconds);
    }

    /**
     * @param mixed $taskId
     * @param int $time 时间戳
     */
    public function delayTaskTo($taskId,$time){
        $this->_driver->setExecTime($taskId,$time);
    }

    /**
     * 监听队列任务
     *
     * @param callable $callback 处理任务的方法 function ($taskId, $taskName, $taskArgs, $taskTags,$taskMeta):bool ;
     * @param int $limitTime 最大执行时间 秒 -1:不限制
     */

    function listen($callback,$limitTime = -1){
        $isCli = (bool)preg_match("/cli/i", php_sapi_name());
        if(!$isCli){
            exit($this->_t('service.pls run in cli',[]));
        }

        $this->_driver->install();

        $this->_msgInfo = true;

        $lockFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.md5(get_class($this->_setting));

        if(!file_exists($this->_signalFile)){
            file_put_contents($this->_signalFile,'');
        }
        chmod($this->_signalFile,0666);


        $overTime = time()+$limitTime;
        $hasOverTime = ($limitTime>=0);

        $lock = new Lock($lockFile);
        while (1) {
            $task = $lock->wait(function(){return $this->_driver->scan(40);});
            if(empty($task)){
                $this->_waitSignal();
            }
            elseif(!isset($task['q_name']) || empty($task['q_name'])) {
                $this->_driver->remove($task['id']);
            }
            else{
                $taskId = $task['id'];
                if(!isset($task['q_tags'])) $task['q_tags'] = '';
                if(!isset($task['q_mate'])) $task['q_tags'] = [];
                $r = call_user_func($callback,$taskId, $task['q_name'], $task['q_args'],$task['q_tags'],$task['q_meta'],$this);
                if ($r) {
                    $this->_driver->remove($taskId);
                }
                if (!is_bool($r)){
                    echo $this->_t('service.return invalid :0',[$task['q_name']])."\n";
                    sleep(3);
                }
            }
            //超时退出当前方法
            if($hasOverTime && time()>$overTime){return ;}
        }
    }


    private function _waitSignal(){
        $lastMark = file_get_contents($this->_signalFile);
        //$firstExecTime = $this->_driver->getFirstTime();

        $processStatus = ['    ','.   ','..  ','... ','....','... ','..  ','.    '];
        $pLen = count($processStatus);
        $i = $this->_driver->getFirstTime()-time();
        $i = min(180,$i>0?$i:180);

        while (true){
            sleep(1);
            if($i<0){//刻意晚一秒
                echo "\r/自动刷新。";
                return;
            }

            if($this->_msgInfo) echo "\r".'Listening: / '.$this->_t('service.waiting :date :i',['date'=>date('Y-m-d H:i:s'),'i'=>$i]).' >'.$processStatus[$i%$pLen];
            $t = file_get_contents($this->_signalFile);
            //有新的数据插入(mark发生变化退出扫描)
            if($t!=$lastMark){
                if($this->_msgInfo) echo "\r/".$this->_t('service.receive new task',[])."                                             \n";
                return;
            }
            $i--;
        }
    }

}