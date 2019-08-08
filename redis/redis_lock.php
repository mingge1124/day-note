<?php
$redis = new Redis();
$redis->connect('127.0.0.1');
var_dump($redis->ping());

//参考：https://redis.io/commands/set
//2.6.0以后，set支持设置过期时间
$unique_random_str = 'xxxx';
$key = 'xxxkey';
$res = $redis->set($key, $unique_random_str, ['NX', 'EX' => 60]);
if ($res) {
	//do something
	var_dump('hell');
	
	//release lock, 为了防止本次连接获得的锁已过期，导致释放了下一个获得锁的连接，因此需要根据锁的值来判定是否属于本次连接，官方文档做法要求是要用脚本代替del，不明白
	if ($unique_random_str == $redis->get($key)) {
		$redis->del($key);
	}
}


//参考：https://redis.io/commands/setnx
//互斥锁
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









