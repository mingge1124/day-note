慢日志
---
#### 执行情况   

1、获取初始化锁的时间不会被统计在执行时间里。日记记录发生在语句执行完成并所有锁释放之后，因此日记记录顺序可能与语句执行顺序有所不同。


#### 系统变量

变量名 | 描述    		
----- | -----    			
long_query_time | 最小值0，默认值10，单位秒，精度到微秒。日志写入文件时，微秒有效，写入表时，微秒无效    				
min_examined_row_limit | 设置至少检查行数，语句必须超过设置值和超时才会被记录    
log_slow_admin_statements，log_queries_not_using_indexes | 默认系统管理（administrative）语句不会被记录，不使用索引的查询也不会被记录。可以设置 log_slow_admin_statements 和 log_queries_not_using_indexes 来改变    
slow_query_log | 是否开启慢日志	[={0|1}]    
slow_query_log_file | 设置慢日志文件   
log_output | 设置日志输出形式，FILE,TABLE   


#### 查询工具 mysqldumpslow

[参考链接](http://www.ywnds.com/?p=9808)