<?php

/**
 * @author Lyons <[superlyons@163.com]>
 */

namespace superlyons\idGenerator;

use \yii\Yii;
use \yii\db\IntegrityException;

/**
 * Twitter的Snowflake算法的实现, 该算法的PHP实现无法保证结果绝对的唯一性, 因此还提供了异常恢复机制
 */
class Snowflake
{
	const EPOCH = 1479533469598;  //change your EPOCH
	const max12bit = 4095;
	const max41bit = 1099511627775;

	/**
	 * 生成一个id, 可以在32bit的PHP环境下运行
	 * @param  int|false $workid 工作id,如果为false则随机生成
	 * @return array
	 */
    public Static function getId($workid=false){
    	$workid = $workid === false ? mt_rand(1,1023) : $workid;
    	$time = floor(microtime(true) * 1000);
		$time -= self::EPOCH;
		$base = bcadd(self::max41bit , $time);
		$movebit = pow(2,22);
		$base = bcmul($base , $movebit);
		$movebit =  pow(2,12);
		$workid =  $workid * $movebit;
		$random = mt_rand(0, self::max12bit);
		$idstep1 = bcadd($base,$workid);
		$id = bcadd( $idstep1 , $random );
		return $id;
    }
    /**
     * 解析id的时间戳部分
     * @param  string $id [getId]生成的id
     * @return string 时间戳
     */
    public Static function getTimeStamp($id){
		$movebit = pow(2,22);
		$time = bcdiv($id, $movebit); 
		$time = $time - self::max41bit + self::EPOCH;
		return $time;
    }
    /**
     * 解析id的workid部分
     * @param  string $id [getId]生成的id
     * @return string Workid
     */
    public static function getWorkid($id){
    	$movebit = pow(2,22);
		$time = bcdiv($id, $movebit); //bit move right 22 
		$time = bcmul($time , $movebit); //bit move left 22
		$step1 = bcsub($id,$time); //workid + random
		$movebit = pow(2,12);
		$workid = bcdiv($step1, $movebit); //bit move right 12
		return $workid;
    }
    /**
     * 解析id的随机数部分
     * @param  string $id [getId]生成的id
     * @return string 随机数
     */
    public static function getRandom($id){
    	$movebit = pow(2,22);
		$time = bcdiv($id, $movebit); 
		$time = bcmul($time , $movebit);
		$step1 = bcsub($id,$time); //workid+random
		$movebit = pow(2,12);
		$workid = bcdiv($step1, $movebit);  //workid

		$workid = bcmul($workid, $movebit);  //bit move left 12
		$random = bcsub($step1, $workid);
		return $random;
    }

    /**
     * 捕获异常重新生成ID
     * 
     * ```
     * Snowflake::particleHandle(function($data){
     * 		//Your Data Handle;
     * },[$data]);
     * ```
     * @param  callable $execute 要执行的回调函数
     * @param  array $params 回调函数的入参
     * @param  string $againName 回调函数入参中接收有关重复ID的参数名
     * @param  integer $repeat 重复生成ID次数
     */
    public Static function particleHandle($execute,$params=[], $againName="again", $repeat=10 ,$again=false){
    	$result = false;
    	if(is_callable($execute)){
    		//default first call $again is false
	    	try{
	    		static::bulidParams($execute, $params, $againName, $again);
	    		$result = call_user_func_array($execute,$params);
	    	}catch (\yii\db\IntegrityException $e) {
	    		
	    		if($again === false){
	    			$again['repeat'] = 1;
	    		}else{
	    			$again['repeat'] += 1;
	    		}

	    		if($again['repeat'] < $repeat){
	            	$result = self::ParticleHandle($execute,$params,$againName,$repeat,$again);
	            }else{
	            	throw $e;
	            }
	        }
    	}
    	return $result;
    }

    protected Static function bulidParams($callable,&$params, $againName, $again){
		$hasParam=false;
		if( is_array($callable) ){
			$c = new \ReflectionClass($callable[0]);
			$fun = $c->getMethod($callable[1]);
		}else{
			$fun = new \ReflectionFunction($callable);
		}
		$funparams = $fun->getParameters();
		
		$i=0;
		foreach($funparams as $param){
			if(strtolower($param->getName()) == strtolower($againName)){
				$hasParam = $i;
				break;
			}
			$i++;
		}

		if( $hasParam !== false ){
			$params[$hasParam] = $again;
			ksort($params);
		}
    }
}
