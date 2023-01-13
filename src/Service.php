<?php
/**
 * @author Roy
 * 2016-03-06 23:32:06
 */
namespace iry\queue;

class Service extends Base
{

    protected $_node;// 如果驱动 使用的数据库则为表名， 使用了Redis则为key
    private $_msgInfo;
    private $_processMsgFunc;

    /**
     * 监听队列任务
     * @param int $limitTime 最大执行时间 秒 -1:不限制
     */

    function listen($callback,$limitTime = -1){
        $isCli = (bool)preg_match("/cli/i", php_sapi_name());
        if(!$isCli){
            exit('请以CLI模式运行运行 / Please run in CLI mode');
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
                $r = call_user_func($callback,$taskId, $task['q_name'], $task['q_args'],(isset($task['q_tags'])?$task['q_tags']:''));
                if ($r) {
                    $this->_driver->remove($taskId);
                }
                if (!is_bool($r)){
                    echo '无效返回值 -- Invalid return value. <TaskName:'.$task['q_name'].'>'."\n";
                    sleep(3);
                }
            }
            //超时退出当前方法
            if($hasOverTime && time()>$overTime){return ;}
        }
    }

    private function _service(){

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

            if($this->_msgInfo) echo "\r".'Listening: / 正在等待任务: ['.date('Y-m-d H:i:s').' / '.$i.'] >'.$processStatus[$i%$pLen];
            $t = file_get_contents($this->_signalFile);
            //有新的数据插入(mark发生变化退出扫描)
            if($t!=$lastMark){
                if($this->_msgInfo) echo "\r/Receive new task.发现新任务。                                             \n";
                return;
            }
            $i--;
        }
    }

}