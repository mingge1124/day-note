## mysql存储引擎innodb读书笔记

### 体系结构
---
> ![结构图](http://images.blogjava.net/blogjava_net/shinewang/mysql_arch.jpg)

* 连接池
* 管理服务和工具
* SQL接口
* 查询分析器
* 优化器
* 缓存和缓冲
* 插件式存储引擎
* 物理文件


### 引擎简介
---
> [不同引擎之间的特性比较](https://dev.mysql.com/doc/refman/5.7/en/storage-engines.html)

1. InnoDB
特点：支持事务，行锁，支持外键，多版本并发控制（MVCC），插入缓冲（insert buffer），二次写（double write），自适应哈希索引（adaptive hash index），预读（read ahead）
存储方式：采用聚集（clustered）的方式，表的存储都是按照主键顺序存放，若表没有定义主键，则会为每一行生成一个6个字节的ROWID作为主键

2. MyISAM
特点：不支持事务，表锁，支持全文索引，缓冲池只缓冲索引文件，
物理文件：MYD文件存放数据，MYI文件存放索引

3. 不常用引擎：NDB，Memory，Archieve，Federated，Maria，Sphinx等等



### InnoDB引擎
---
多线程：InnoDB存储引擎是多线程的模型
1. Master Thread：负责将缓冲池中的数据异步刷新到磁盘，保证数据的一致性（包含脏页的刷新，合并插入缓冲，UNDO页的回收等）

2. IO Thread：在Innodb存储引擎中大量使用了AIO(异步IO)来处理写IO请求，IO线程主要负责这些IO请求的回调处理
四类IO线程：insert buffer thread，log thread，read thread，write thread
`show engine innodb status;`

3. Purge Thread：事务提交后，需要该线程来回收已经使用并分配的undo页（内存页）
`show variables like 'innodb_purge_threads';`

4. Page Cleaner Thread：将之前版本中脏页的刷新操作都放入到单独的线程中完成，目的是为了减轻原主线程的工作以及对于用户查询线程的阻塞

#### 关键特性

##### 插入缓冲（Insert Buffer）
概念：不是缓冲池的一部分，和数据页一样，是物理页的一部分


#### 缓冲池
概念：简单来说就是一片内存空间，以页为单位缓存数据，解决了CPU速度和磁盘速度不对等的交互，提高了引擎处理数据的性能2
结构图：![UPrB0f.md.png](https://s1.ax1x.com/2020/07/06/UPrB0f.md.png)
配置:
```
innodb_buffer_pool_size //缓冲池大小
innodb_old_blocks_pat   //老生代占整个LRU链长度的比例，默认是37，即新:老=63:37
innodb_old_blocks_time  //老生代停留时间，单位是毫秒，默认1000，即同时满足被访问和在老生代停留超过1秒两个条件，就可移动到新生代头部
```

##### 工作原理
预读：磁盘读写，不是按需读取，而是按页读取，一次至少读取一页，因为数据访问一般是集中读写，提前加载相邻的数据，可以减少磁盘IO。
[优化的LRU算法](https://www.cnblogs.com/myseries/p/11307204.html)：
1. 链表分新老生代，每次新插入是放在老生代的头部，解决预读失效问题
2. 设定老生代停留时间，需满足被访问和在老生代停留超过1秒两个条件，方可移动到新生代头部，解决缓存污染问题

#### 重做日志缓冲（redo log buffer）
流程：引擎将重做日志信息存入重做日志缓冲区，然后以一定频率（一般为1秒）存到重做日志文件
配置：
```
innodb_log_buffer_size //重做日志缓冲大小
```
重做日志缓冲刷新到重做日志的触发条件：
* Matser Thread 每一秒会执行刷新
* 每个事务提交时会执行刷新
* 当重做日志缓冲区剩余空间小于1/2时，会执行刷新


#### 额外的内存池




#### checkpoint

避免发生数据丢失的问题（从缓冲池将页的新版本刷新到磁盘发生了宕机，数据就无法恢复），当前事务数据库系统采用了Write
Ahead Log策略，即当事务提交时，先写重做日志，再修改页。当由于发生宕机而引起数据丢失时，可以通过重做日志来恢复

checkpoint技术的目的：
1. 缩短数据库的恢复时间：数据库发生宕机时，因为Checkpoint之前的页都已经刷回磁盘，只需对checkpoint后的重做日志进行恢复
2. 缓冲池不够用时，将脏页刷新到磁盘：不够用时，根据LRU算法会溢出最近最少使用的页，若为脏页，则强制执行checkpoint，将脏页刷回磁盘
3. 重做日志不可用时，刷新脏页

对于Innodb而言，其是通过LSN(Log Sequence Number)来标记版本的，LSN是8字节的数字，每个页有LSN，重做日志页游LSN，Checkpoint也有LSN


分类：
1. Sharp Checkpoint
2. Fuzzy Checkpoint
  2.1 Master Thread Checkpoint
  2.2 FLUSH_LRU_LIST Checkpoint
  2.3 Async/Sync Flush Checkpoint
  2.4 Dirty Page too much Checkpoint


#### 主线程工作原理
主线程具有最高的线程优先级别，内部由多个循环（loop组成）：
1 主循环（loop）
2 后台循环（backgroup loop）
3 刷新循环（flush loop）
4 暂停循环（suspend loop）


### 文件结构
---

#### 参数文件
---
动态参数：可在实例的生命周期更改
```
set global system_variable_name=value;
set session system_variable_name=value;
set @@global.system_variable_name=value;
set @@session.system_variable_name=value;

select @@global.system_variable_name;
select @@global.system_variable_name;
```


静态参数：在实例的声明周期不可更改

#### 日志文件

##### 二进制日志(binary log)
概念：记录了对MYSQL数据库的所有更改操作，不包含select和show这类操作、

作用：
* 恢复: 服务器崩溃，日志恢复
* 复制：主从复制
* 审计：审计日志，可以判断是否有对数据库进行注入操作

配置：
```
#查看大概配置
show variables like "%bin%";
```

关键配置：
* max_binlog_size 指定了单个二进制日志文件的最大值，超过该值，则生成新的日志文件，后缀名+1，并记录文件名到.index文件
* binlog_cache_size 
  当使用事务时，未提交的二进制日志都会被记录到一个缓冲中，等提交时再统一将缓冲刷到日志文件上，该参数决定了缓冲的大小，但由于该参数是基于session的，因此不宜过大，否则可能会造成内存溢出。
  当一个事务记录大于设定的缓冲的大小时，mysql会将缓冲区的日志写入一个临时文件（增加io），因此该值又不能设置过小。可通过`show global status like "binlog_cache%"`查看binlog_cache_disk_use
  （使用临时文件写二进制日志的次数）和binlog_cache_use（使用缓冲写二进制日志的次数）来判断设置值是否合理
* sync_binlog 默认情况下，二进制不是每次写的时候都是直接写磁盘，一般是先写缓冲，因此，数据库宕机时，可能会导致丢失。sync_binlog=N表示每写N次缓冲就刷新到磁盘
* binlog_format 日志格式，statement|row|mixed，建议采用mixed，[差异](https://www.cnblogs.com/langtianya/p/5504774.html)

查看二进制日志文件指令：mysqlbinlog


##### 慢查询日志
```
#设置查询时长大于该参数时记录慢日志
show variables like "long_query_time";

#开启慢日志
show variables like "log_slow_queries";

#慢日志路径
show variables like "slow_query_log_file";

#设置记录没有使用索引的sql
show variables like "log_queries_not_using_indexes";

#设置输出格式,file/table
show variables like "log_output";
```

查看慢日志文件指令：mysqldumpslow，mysqlsla


##### 查询日志
```
show variables like "general_log";
```

##### 错误日志
```
#文件路径
show variables like "%log_error%";
```

#### 套接字文件（socket文件）
本地链接可使用UNIX域套接字方式
```
#查看文件路径
show variables like "%socket%";

#socket方式链接
mysql -S '/tmp/myqsl.socket' -p
```
#### pid文件
记录mysql的进程id
```
#查看文件路径
show variables like "%pid_file%";
```

#### 表结构定义文件
各类插件式存储引擎的存储结构不同，因此表结构定义文件也不同。


### 表
---

#### Innodb逻辑存储结构
---
![Uh7ZX6.png](https://s1.ax1x.com/2020/07/20/Uh7ZX6.png)

表空间（tablespace）
表空间分共享表空间ibdata1和独立表空间（每张表内的数据可以单独放到一个文件上），启用参数`innodb_file_per_table`开启独立表空间，
独立表空间只存放数据，索引和插入缓冲Bitmap页。其他如回滚（undo）信息，插入缓冲索引页，系统事务信息，二次写缓冲还是放在共享表空间

段（segment）
表空间由各种段组成，常见段有数据段，索引段，回滚段等，数据段为B+树的叶子节点（Leaf node segment），索引段为非叶子节点（Non-leaf node segment）

区（extent）
区由连续页组成，每个区大小为1M，InnoDB默认页大小为16KB，即一个区一般有64个连续页
注意，在每个段开始时，先用32个页大小的碎片页（fragment page）来存放数据，使用完之后再申请64个连续页，这样对于一些小表可以节省磁盘空间


页（page）
页是InnoDB磁盘管理的最小单位，也可称为块，默认大小为16KB，可通过`innodb_page_size`设置大小。常见页类型：
* 数据页（B-tree Node）
* undo页（undo log page）
* 系统页（System Page）
* 事务数据页（Transaction system page）
* 插入缓冲位图页（Insert Buffer Bitmap）
* 插入缓冲空闲列表页（Insert Buffer Free List）

行（row）
InnoDB是面向行（row-oriented）的，就是数据按行进行存放，每个页最多允许存放16KB / 2 - 200行的记录，即7992行记录。

#### InnoDB行记录格式
> [参考](https://www.cnblogs.com/yungyu16/p/12940451.html#dynamic%E5%92%8Ccompressed)
值：compact|Redundant|Compressed|Dynamic
查看表使用的行记录格式：`show table status like "table_name"`

#### InnoDB数据页结构
主要由以下7个部分组成
1. File Header（文件头）固定38字节
2. Page Header（页头）固定56字节
3. Infimun 和 Supremum Records
4. User Records（用户记录，即行记录）
5. Free Space（空闲空间）
6. Page Directory（页目录）
7. File Trailer（文件结尾信息）固定8字节
（待学习细节）


### 索引与算法
(待学习)

### 锁

#### 常用锁
共享锁（S锁，share lock）：允许事务读一行数据
排他锁（X锁，exclusive lock）：允许事务删除或更新一行数据
意向锁（intention lock）：将锁定的对象分为多个层次（表，页，行...），意味着事务希望在更细力度上进行加锁
意向共享锁（IS lock）：事务希望获得一张表中的某几行的共享锁
意向排他锁（IX lock）：事务希望获得一张表中的某几行的排他锁


共享锁只和共享锁兼容，X锁是都不兼容，兼容的意思是事务a获得了行r的S锁时，事务b也可以立即获得它的S锁（主要是数据没变化）

InnoDB中锁的兼容性
||IS|IX|S|X|
|:---:|:---:|:---:|:---:|:---:|
|IS|兼容|兼容|兼容|不兼容|
|IX|兼容|兼容|不兼容|不兼容|
|S|兼容|不兼容|兼容|不兼容|
|X|不兼容|不兼容|不兼容|不兼容|

#### 一致性非锁定读 vs 一致性锁定读


#### 锁算法
record lock（行锁）：单个行记录上的锁
gap lock（间隙锁）：间隙锁，锁定一个范围，但不包含记录本身
Next-Key Lock (Gap Lock + Record Lock)：锁定一个范围，并且锁定记录本身































































































