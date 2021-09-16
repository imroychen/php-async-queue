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
//        else{
//            $time = intval($data['q_exec_time']);
//            return $this->_exec($this->_sql('update {{:table}} set q_exec_time='.$time),'update');
//        }
        return $queueId;
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

    public function install(){
        /*
        $result = $this->query("SHOW TABLES LIKE {{:table}}");
        $createSql = <<<SQL
CREATE TABLE `{{:table}}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `q_sign` char(32) CHARACTER SET latin1 NOT NULL,
  `q_name` varchar(100) CHARACTER SET latin1 NOT NULL,
  `q_args` text CHARACTER SET latin1 NOT NULL COMMENT '//{}',
  `q_exec_time` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `q_exec_time` (`q_exec_time`),
  KEY `q_sign` (`q_sign`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=135 DEFAULT CHARSET=utf8;
SQL;
        $this->_exec($createSql);
        */
    }
}