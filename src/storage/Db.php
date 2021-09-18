<?php

namespace iry\queue\storage;
// todo 适合非大型项目（单服务器小内存推荐）
// use think\facade\Db;

abstract class Db extends Base
{
    private $_table;


    protected function _init($args,$rawArgs)
    {
        $this->_table = (isset($args['table']) && !empty($args['table']))? preg_replace('/[^\w\-]+/','',$args['table']):'ir-queue-storage';
    }

    /**
     * 查询一条SQl
     * @param $sql
     * @return array[] [[msg1],[msg2]]
     */
    abstract protected function _query($sql);

    /**
     * 执行一条SQL
     * @param string $sql
     * @param string $sqlType 'insert/update/delete'
     * @return bool
     */
    abstract protected function _exec($sql,$sqlType);


    private function _sql($sql){
        return str_replace('{{:table}}',$this->_table,$sql);
    }

    /**
     * @param $sql
     * @return array
     */
    private function _getRecord($sql){
        $data = $this->_query($sql);
        return (!empty($data) && !empty($data[0]))? $data[0]:[];
    }


    /**
     * 检查任务是否存在
     * @param string $sign
     * @return int|bool $queueId|false;
     */
    public function exists($sign){
        $queueId = false;
        if(!empty($sign)){
            $res = $this->_getRecord($this->_sql('select `id` from {{:table}} where q_sign='.var_export(strval($sign),true) .' limit 0,1'));
            $queueId = (!empty($res) && isset($res['id']))?$res['id']:false;
        }
        return $queueId;
    }

    /**
     * 创建一个新的队列任务
     * @param array $data [
     *   'q_name'=>$name,
     *   'q_args'=>$args,
     *   'q_exec_time'=>$execTime,
     * ]
     * @param bool $unique
     * @return int|bool $queueId|false;
     */
    public function create($data,$unique){

        $sign = $this->createSign($data);
        $queueId = $unique?$this->exists($sign):false;

        if(!$queueId) {
            return $this->_create($data, $sign);
        }
//        else{
//            $time = intval($data['q_exec_time']);
//            return $this->_exec($this->_sql('update {{:table}} set q_exec_time='.$time),'update');
//        }

        return $queueId;
    }
    public function _create($data,$sign){
            $fields = [];
            $values = [];
            $data['q_sign'] = $sign;
            $data['q_args'] = serialize($data['q_args']);
            foreach ($data as $f => $v) {
                $fields[] = '`' . $f . '`';
                $values[] = var_export($v, true);
            }
            $fieldsStr = implode(',',$fields);
            $valuesStr = implode(',',$values);
            return $this->_exec($this->_sql('insert into {{:table}} (' . $fieldsStr . ') values (' . $valuesStr.')'),'insert');
    }



    /**
     * 移除任务
     * @param $id
     * @return bool
     */
    public function remove($id)
    {
        $id = var_export($id,true);
        $r = $this->_exec($this->_sql('delete from {{:table}} where `id`='.$id),'delete');
        return $r!==false;
    }

    private function _setExecTime($id,$time){
        $id = var_export($id,true);
        $this->_exec($this->_sql('update {{:table}} set `q_exec_time`='.(time()+30).' where id='.$id),'update')!==false;//锁定30S
    }

    /**
     * 按照先进先出的原则 返回一条时间到了的数据
     * @return array
     */

    public function scan($lockTime=40){
        $time = time();
        $r =  $this->_getRecord($this->_sql('select * from {{:table}} where `q_exec_time`<'.$time.' order by `q_exec_time`'.' limit 0,1'));
        if(is_array($r) && !empty($r)) {
            if($lockTime>0) {
                $this->_setExecTime($r['id'],$time+$lockTime);
            }
            $r['q_args'] = empty($r['q_args'])? []:unserialize($r['q_args']);
            return $r;
        }
        return [];
    }

    /**
     * 获取第最早的任务执行时间
     * @return int $r
     */

    public function getFirstTime(){
        $data =  $this->_getRecord($this->_sql('select `q_exec_time` from {{:table}} order by `q_exec_time`'.' limit 0,1'));
        if(empty($data)){
            return -1;
        }else{
            $time = $data['q_exec_time']*1;
            return ($time>0?$time:0);
        }
    }

    /**
     * 根据数据创建签名
     * @param $data
     * @return string $sign
     */

    public function createSign($data){
        return md5($data['q_name'].'//'.var_export($data['q_args'],true));
    }


    //该方法有Service调用
    public function install(){

        $createSql = <<<SQL
CREATE TABLE IF NOT EXISTS {{:table}} (
     `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
     `q_sign` char(32)  NOT NULL,
     `q_name` varchar(100)  NOT NULL,
     `q_args` text  NOT NULL COMMENT '//{}',
     `q_tags` varchar(100)  NOT NULL DEFAULT '',
     `q_exec_time` int(11) unsigned NOT NULL  DEFAULT 0,
     PRIMARY KEY (`id`),
     KEY `q_exec_time` (`q_exec_time`),
     KEY `q_sign` (`q_sign`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=101 DEFAULT CHARSET=utf8;
SQL;
        //$table = $this->_query($this->_sql("SHOW TABLES"));
        //if(!in_array($this->_table,$table)) {
        $createTable = $this->_exec($this->_sql($createSql), 'create_table');
        //}

        $sign = md5('VERSION');
        if($createTable===false){
            echo "\n数据表不存在, 请先手动创建";
            echo "\nTable does not exist.Please create";
            echo "\n----------------------------------\n";
            echo "\n".$this->_sql($createSql)."\n\n";
            echo "\n----------------------------------\n";
        }elseif($createTable>0){
            $this->_create(['q_name'=>'VERSION','q_args'=>['version'=>$this->_getVersion()],'q_exec_time'=>time()+86400*365*20],$sign);
        }else{
            /*
            //$qid = $this->exists($sign);
            $res = $this->_getRecord($this->_sql('select `q_args` from {{:table}} where q_sign='.var_export(strval($sign),true) .' limit 0,1'));
            $args = (isset($res['q_args']) && !empty($res['q_args']))? unserialize($res['q_args']):[];
            $version = isset($args['version'])?$args['version']:'';
            //升级版本
            */
        }
    }
}