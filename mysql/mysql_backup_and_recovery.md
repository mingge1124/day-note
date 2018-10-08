备份与恢复
---

#### 物理与逻辑备份

1、物理备份：指备份存储数据库内容的目录和文件，适用于大型的，重要的数据库，在问题出现时可以迅速恢复。特点：    
* 恢复时无需装换
* 输出比逻辑备份小
* 备份和恢复粒度由整个数据库级别到单个文件级别。may or may not 提供table-level粒度。因为innodb表可以由单个文件组成，也可共享一个文件。每个MySIAM表一般由几个文件组成    
* 便携性只能针对具有相同或类似的硬件特性
* 只能在mysql服务器不运行时使用，或者适当的锁住，使数据库在备份是不变化

2、逻辑备份：指逻辑数据库结构（create database, create table语句）和内容（insert语句和分隔的文本（delimited-text files））,适用于修改小数量级别的数据值或表结构，或在一个不同的机器结构重建数据。特点:   
* 比物理备份慢    
* 输出比物理备份大，特别是用文本格式保存时   
* 粒度包含服务器级别（all databases），数据库级别（all tables in a particular database），或表级别。无视存储引擎类型   
* 不包含日志或配置文件，或者其他数据库相关文件
* 保存格式是机器独立并高度便携的
* 可在服务器执行时操作


#### 线上与线下备份 

1、概念：又称热备（hot backup）和冷备（cold backup），还有warn backup    
* 热备：MySQL server is running
* 冷备：MySQL server is stopped
* warn backup：MySQL server remains running but locked against modifying data
2、特点：
* 热备：备份对其他客户端干扰小，可以连接mysql服务器，并可以根据需要执行的操作决定是否能访问数据；必须小心加锁，以防发生数据修改，从而影响备份完整性    
* 冷备：客户端会因为备份期间服务器不可见受影响，所以往往是用从库来做备份，从而不影响可用性；备份过程是简单的，因为不会搜到客户端干扰
3、恢复：
线上和离线恢复有类似的区别，特点的区别也类似，但是线上恢复比线上备份需要更强的锁定，客户端在备份期间不允许访问数据。

#### 本地和远程备份

#### 快照备份

#### 全量备份与增量备份
1、概念：全量备份指在指定时间点Mysql服务器管理的所有数据，增量备份指的是在指定时间段内记录的所有数据修改（例子：binary log）

#### 全量恢复与增量恢复


备份方法
-----
1、mysqldump

2、复制表文件：MyISAM 表支持，没啥用

3、Delimited-Text(分隔文本)文件：
* `select * into outfile 'file_name' from table_name`    
* mysqldump 带上 --tab 选项
* 重载delimited-text数据文件可以用`LOAD_DATA_INFILE`或mysqlimport

4、使用从库进行备份

5、恢复损坏表：对于MyISAM表，使用`repair table`或者`myisamchk -r`，可以应对99.9%的情况
