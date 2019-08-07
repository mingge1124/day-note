<?php
//参考：https://redis.io/commands/setnx

//互斥锁
$redis = new Redis();
$redis->connect('127.0.0.1');
var_dump($redis->ping());

$timeout = 300;
$lock_time = time() + $timeout + 1;
$lock_name = 'lock';

$lock = $redis->setnx($lock_name, $lock_time);
if ($lock == 1 || (time() > $redis->get($lock_name) && time() > $redis->getset($lock_name, $lock_time))) {
	var_dump('lock success');
	//do something
	var_dump('do something');
	
	//release lock 只释放未失效的锁
	if(time() < $lock_time) {
		$redis->del($lock_name);
	}
	
} else {
	var_dump('lock fail');
	var_dump($redis->get($lock_name) - time());
}

//互斥锁--循环获取，直到取到锁位置
$lock = 0;
while($lock != 1) {
	$time = time();
	$lock_time = $time + $timeout + 1;
	$lock = $redis->setnx($lock_name, $lock_time);
	if ($lock == 1 || (time > $redis->get($lock_name) && $time > $redis->getset($lock_name, $lock_time))) {
		var_dump('lock success');
		//do something
		var_dump('do something');
		
		break;
	} else {
		//随机延时重试
		var_dump('lock fail');
		$num = rand(1, 100) / 10000;
		usleep($num);
	}
}
//release lock 只释放未失效的锁
if(time() < $lock_time) {
	$redis->del($lock_name);
}





