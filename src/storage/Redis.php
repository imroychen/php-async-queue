<?php
namespace iry\queue\storage;
// todo 适合大及超大型项目（Web服务器有足够的内存或者有独立的Redis服务器推荐）
class Redis extends Base
{
    protected $_redis;
    protected $_dataset;
    /**
     * Redis constructor.
     * @param array $args Example: [host=>'..',port=>'..',....]
     * @param string $rawArgs  Example: host=localhost&port=6379&key=my_date_key&password=my_password
     */
    protected function _init($args,$rawArgs)
    {
        $this->_dataset = (isset($args['key']) && !empty($args['key']))? preg_replace('[^\w\-]','',$args['key']):'ir-e-store';
        $this->_redis = new \Redis();
        try {
            $this->_redis->connect($args['host'], (isset($args['port']) ? $args['port'] : 6379));
            if (isset($args['password'])) {
                $this->_redis->auth($args['password']);
            }
        }catch (\RedisException $e){
            //echo $e->getMessage();
            echo "Unable to connect to Redis / 无法连接Redis\n";
            exit;
        }
    }


    /**
     * 检查任务是否存在
     * @param string $sign
     * @return string|bool $queueId|false;
     */

    public function exists($sign){
        $result = $this->_redis->get($sign);
        return empty($result)?false:$result;
    }

    /**
     * 创建一个新的队列任务
     * @param array $data [
     *   'q_name'=>$name,
     *   'q_args'=>$args,
     *   'q_exec_time'=>$execTime,
     * ]
     * @param bool $unique 是否去重
     * @return string|bool $queueId|false;
     */

    public function create($data,$unique){
        if (!empty($data)) {
            $time = $data['q_exec_time']<1 ? (time()-1) : intval($data['q_exec_time']);
            if(!$unique) $data['_'] = uniqid().'/'.mt_rand(10,99);
            $str = json_encode($data);
            $this->_redis->zAdd($this->_dataset, $time,$str);
            return $str;
        }
        return false;
    }

    /**
     * 移除任务
     * @param $id
     * @return bool
     */
    public function remove($id)
    {
        $res = $this->_redis->zRem($this->_dataset, $id);
        return (bool)$res;
    }

    /**
     * 按照先进先出的原则 返回一条时间到了的数据
     * @return array
     */

    public function scan(){
        $records = $this->_redis->zRange($this->_dataset, 0, 0, true);

        if(!empty($records)) {
            foreach ($records as $text=>$score) {
                $res = json_decode($text, true);
                $res = is_array($res) ? $res : [];
                $res['id'] = $text;
                return  $res;
            }
        }
        return false;
    }

    /**
     * 获取第最早的任务执行时间
     * @return int $r
     */

    public function getFirstTime(){
        $record = $this->_redis->zRange($this->_dataset, 0, 1, true);
        if(!empty($record)) {
            $item = current($record);
            //$r = $this->_redis->zScore($this->_dataset, $item);
            //return $r*1;
            return $item*1;
        }else{
            return -1;
        }
    }

    /**
     * 根据数据创建签名
     * @param $data
     * @return string $sign
     */

    public function createSign($data){
        return json_encode($data);
    }
}