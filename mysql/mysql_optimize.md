MYSQL 优化
---

> 数据库级别     
> 硬件级别     
> 平衡可移植性与性能     


#### 数据库级别

因素：   
1、表结构是否合理，（字段，字段数据类型...）    
2、索引是否合理     
3、存储引擎是否合理，（例如innodb为支持事务，myisam不支持事务)    
4、表是否使用合适的行格式（row format，由存储引擎决定）     
5、应用是否使用合理的锁策略（locking strategy，与存储引擎相关）     
6、缓存所使用的内存大小是否合理    


#### 硬件级别

瓶颈：     
1、Disk seeks（磁盘寻址）    
2、Disk reading and writing（磁盘读写）
3、CPU cycles（cpu周期）     
4、Memory bandwidth（内存带宽）

