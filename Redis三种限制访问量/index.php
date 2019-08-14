<?php

/***计数器法***/
function isActionAllowed($userId, $action, $period, $maxCount)
{
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $key = sprintf('hist:%s:%s', $userId, $action);
    $now = msectime();   # 毫秒时间戳

    $pipe=$redis->multi(Redis::PIPELINE); //使用管道提升性能
    $pipe->zadd($key, $now, $now); //value 和 score 都使用毫秒时间戳
    $pipe->zremrangebyscore($key, 0, $now - $period); //移除时间窗口之前的行为记录，剩下的都是时间窗口内的
    $pipe->zcard($key);  //获取窗口内的行为数量
    $pipe->expire($key, $period + 1);  //多加一秒过期时间
    $replies = $pipe->exec();
    return $replies[2] <= $maxCount;
}

for ($i=0; $i<20; $i++){
    var_dump(isActionAllowed("110", "reply", 60*1000, 5)); //执行可以发现只有前5次是通过的
}
//返回当前的毫秒时间戳
function msectime(){
    list($msec, $sec) = explode(' ', microtime());
    $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
}
/***计数器法***/



/***漏桶法***/
class Funnel {

    private $capacity;
    private $leakingRate;
    private $leftQuote;
    private $leakingTs;

    public function __construct($capacity, $leakingRate)
    {
        $this->capacity = $capacity;    //漏斗容量
        $this->leakingRate = $leakingRate;//漏斗流水速率
        $this->leftQuote = $capacity; //漏斗剩余空间
        $this->leakingTs = time(); //上一次漏水时间
    }

    public function makeSpace()
    {
        $now = time();
        $deltaTs = $now-$this->leakingTs; //距离上一次漏水过去了多久
        $deltaQuota = $deltaTs * $this->leakingRate; //可腾出的空间
        if($deltaQuota < 1) {
            return;
        }
        $this->leftQuote += $deltaQuota;   //增加剩余空间
        $this->leakingTs = time();         //记录漏水时间
        if($this->leftQuota > $this->capacaty){
            $this->leftQuote - $this->capacity;
        }
    }

    public function watering($quota)
    {
        $this->makeSpace(); //漏水操作
        if($this->leftQuote >= $quota) {
            $this->leftQuote -= $quota;
            return true;
        }
        return false;
    }
}


$funnels = [];
global $funnel;

function isActionAllowed($userId, $action, $capacity, $leakingRate)
{
    $key = sprintf("%s:%s", $userId, $action);
    $funnel = $GLOBALS['funnel'][$key] ?? '';
    if (!$funnel) {
        $funnel  = new Funnel($capacity, $leakingRate);
        $GLOBALS['funnel'][$key] = $funnel;
    }
    return $funnel->watering(1);
}

for ($i=0; $i<20; $i++){
    var_dump(isActionAllowed("110", "reply", 15, 0.5)); //执行可以发现只有前15次是通过的
}
/***漏桶法***/




/***令牌桶***/
class TrafficShaper
{
    private $_config; // redis设定
    private $_redis;  // redis对象
    private $_queue;  // 令牌桶
    private $_max;    // 最大令牌数

    /**
     * 初始化
     * @param Array $config redis连接设定
     */
    public function __construct($config, $queue, $max)
    {
        $this->_config = $config;
        $this->_queue = $queue;
        $this->_max = $max;
        $this->_redis = $this->connect();
    }

    /**
     * 加入令牌
     * @param Int $num 加入的令牌数量
     * @return Int 加入的数量
     */
    public function add($num = 0)
    {
        // 当前剩余令牌数
        $curnum = intval($this->_redis->lSize($this->_queue));
        // 最大令牌数
        $maxnum = intval($this->_max);
        // 计算最大可加入的令牌数量，不能超过最大令牌数
        $num = $maxnum >= $curnum + $num ? $num : $maxnum - $curnum;
        // 加入令牌
        if ($num > 0) {
            $token = array_fill(0, $num, 1);
            $this->_redis->lPush($this->_queue, ...$token);
            return $num;
        }
        return 0;
    }

    /**
     * 获取令牌
     * @return Boolean
     */
    public function get()
    {
        return $this->_redis->rPop($this->_queue) ? true : false;
    }

    /**
     * 重设令牌桶，填满令牌
     */
    public function reset()
    {
        $this->_redis->delete($this->_queue);
        $this->add($this->_max);
    }

    private function connect()
    {
        try {
            $redis = new Redis();
            $redis->connect($this->_config['host'], $this->_config['port'], $this->_config['timeout'], $this->_config['reserved'], $this->_config['retry_interval']);
            if (empty($this->_config['auth'])) {
                $redis->auth($this->_config['auth']);
            }
            $redis->select($this->_config['index']);
        } catch (\RedisException $e) {
            throw new Exception($e->getMessage());
            return false;
        }
        return $redis;
    }
}

$config = array(
    'host' => 'localhost',
    'port' => 6379,
    'index' => 0,
    'auth' => '',
    'timeout' => 1,
    'reserved' => NULL,
    'retry_interval' => 100,
);
// 令牌桶容器
$queue = 'mycontainer';
 // 最大令牌数
$max = 5;
// 创建TrafficShaper对象
$oTrafficShaper = new TrafficShaper($config, $queue, $max);
// 重设令牌桶，填满令牌
$oTrafficShaper->reset();
// 循环获取令牌，令牌桶内只有5个令牌，因此最后3次获取失败
for ($i = 0; $i < 8; $i++) {
    var_dump($oTrafficShaper->get());
}
// 加入10个令牌，最大令牌为5，因此只能加入5个
$add_num = $oTrafficShaper->add(10);
var_dump($add_num);
// 循环获取令牌，令牌桶内只有5个令牌，因此最后1次获取失败
for ($i = 0; $i < 6; $i++) {
    var_dump($oTrafficShaper->get());
}
/***令牌桶***/



//echo strtotime(date("Y-m-d 00:00:00",'1562027608'));
/*
function array_unset_tt($arr,$key){     
            //建立一个目标数组  
            $res = array();        
            foreach ($arr as $kk=>$value) {           
               //查看有没有重复项  
               if(isset($res[$value[$key]])){  
                  unset($value[$key]);  //有：销毁  
               }else{    
                  $res[$value[$key]] = $value;  
               }    
            }  
            return $res;  
        }

function uniquArr($array){
    $result = array();
    foreach($array as $k=>$val){
        $code = false;
        foreach($result as $_val){
            if($_val['classid'] == $val['classid']){
                $code = true;
                break;
            }
        }
        if(!$code){
            $result[]=$val;
        }
    }
    return $result;
}		



$a = [
	'0'=>[
		'classid' => 'ab57fc172bf3439295117b273d597e11',
		'subjectid' => '432b3b9e97494d188657a36684fe0d5e3',
		'subjectname' => '少先队s活动',
		'classname' => '四年级(2)班'
	],
	'1'=>[
		'classid' => 'ab57fc172bf3439295117b273d597e11',
		'subjectid' => '432b3b9e97494d188657a36684fe0d5e',
		'subjectname' => '少先队23活动',
		'classname' => '四年级(2)班'
	],
	'2'=>[
		'classid' => 'ab57fc172bf3439295117b273d597e11',
		'subjectid' => '432b3b9e97494d188657a36684fe0d53e',
		'subjectname' => '少先34队活动',
		'classname' => '四年级(2)班'
	]
];


echo "<pre>";
print_r(uniquArr($a));
*/
