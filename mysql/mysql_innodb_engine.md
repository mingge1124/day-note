Innodb Engine
---

#### 简介
通用存储引擎，高可靠性，高性能，从版本MySQL 5.5.5开始作为默认引擎，之前是MyiSAM

#### 核心优点
1、DML(数据操作语言)操作遵循ACID（原子性，一致性，隔离性，持久性）模型，具有commit，rollback的事务和crash-recovery（灾难恢复）功能用于保护用户数据

2、Row-level(行级)锁和类Oracle的一致性读（consistent reads）提高多用户的并发性和性能

3、基于primary keys优化查询。每个innodb表有一个主键被称为聚集索引（clustered index），负责组织数据使得在主键查找时最小化I/O消耗

4、为了保证数据完整性，innodb 支持外键（FORERGN KEY）约束

#### 使用InnoDB表的好处

1、服务器由于硬件或软件问题而崩溃时，重启数据库后，InooDB的crash recovery机制会自动恢复现场

2、Innodb存储引擎维护自己的缓冲池（buffer pool），在访问数据时将表和索引数据缓存在主存储器中。经常使用过的数据直接在内存中处理。
   此缓存适用于许多类型的信息并加快处理速度。在专业化数据库服务器，超过80%的物理内存常常分配给缓冲池

3、外键强制执行参照完整性

4、数据在磁盘或内存中损坏后，checksum 机制会在你使用数据前提醒你


5、自动优化包含主键字段的操作

6、inserts,updates,deletes会被一个自动化机制change buffering优化。InnoDB不仅允许对同一个表并行读和写，还可以缓存changed data以简化磁盘I/O

7、性能优化不受限于长时间运行的大数据表。当从表中反复访问相同的行时，一个称为自适应哈希索引（Adaptive Hash Index）的功能接管使这些查找更快，就像它们来自哈希表一样。

8、可以压缩表和相关索引

9、创建或删除索引对性能和可用性影响更小

10、通过查询INFOMATION_SCHEMA表来监控存储引擎的内部工作

11、通过查询Performance Schema表来监视存储引擎的性能详细信息。

12、专为处理大量数据时的CPU效率和最高性能而设计

13、可以处理大量数据，即使在文件大小限制为2GB的操作系统上也是如此


#### InnoDB Multi-Versioning

InnoDB是一个多版本引擎（multi-versioned storage engine）:它保留了变更列的旧版本信息，以支持事务功能，例如并发和回滚。这些信息作为一个被称为回滚段（rollback segment）的数据结构（在Oracle使用了类似的数据结构后）存储在表空间内。Innodb会在事务回滚中使用这些信息执行需要撤销的操作，它也可以使用这些信息为一致性读（consistent read）建立早期版本。

在内部，InnoDB在每行增加了三个字段存储在数据库中。6个字节,名为DB_TRX_ID的字段用于表示事务标识符（标示插入或更新该行的最后事务），同时，删除操作在内部被认为是一个更新操作，在行（row）中用一个特殊位（special bit）标识它已被删除。7个字节，DB_ROLL_PRT字段被称为roll pointer(滚动指针)。滚动指针指向写入回滚段的撤消日志记录。如果行被更新，撤销日志便会记录该行更新前的内容。6个字节，DB_ROW_ID字段存的是新插入的行而单调递增的row ID。如果InnoDB自动生成一个聚集索引，该索引会包含row ID的值，否则，DB_ROW_ID列不会出现在任何索引中。

在回滚段的撤销日志被分成插入或更新两种日志。插入撤销日志只用在事务回滚中，在事务提交后会立即被丢弃。更新撤销日志同时用在一致性读，但是只有在InnoDB没有分配快照的事务之后才能丢弃它们，在一致读取中可能需要更新撤消日志中的信息来构建数据库行的早期版本。

需要定期提交事务，包含那些只发出一致性读的事务。否则，InnoDB无法丢弃更新撤销日志，导致回滚段太大，挤满表空间。

回滚段中的撤消日志记录的物理大小通常小于相应的插入或更新的行。您可以使用此信息计算回滚段所需的空间。

在InnoDB多版本结构中，行不会在执行删除语句后立即执行物理删除，只有当他丢弃写入删除操作的更新撤销日志之后才会物理删除该行和索引。这个removal操作称为purge（清除），它执行非常快，通常和sql语句执行删除操作有相同的时间顺序

当在一个表中以相同的速率进行小批量的insert和delete行时，purge线程开始落后，然后由于所有“dead”行，表变得越来愈大，使得所有都进行磁盘绑定，变得非常慢。在这种情况下，需要适当限制新行操作，通过调整innodb_max_purge_lag系统变量来分配更多资源给清除线程


#### 多版本和二级索引（Secondary Indexes）

二级索引：除聚簇索引以外，其他索引都称为二级索引，又称辅助索引。

InnoDB多版本并发控制（multiversion concurrency control）区别对待二级索引和聚簇索引。在聚簇索引中的记录会就地更新，并且它们隐藏系统列指向了undo log条目，可以用来重建早起版本的记录。而二级索引都没有这两种特性。

当一个二级索引列被更新后，旧二级索引记录被标记为删除，然后插入新记录，最后标记为删除的记录被删除。当二级索引记录被标记为删除或被一个新事务更新，InnoDB会在聚簇索引中查找数据库记录。在聚集索引中，如果记录在读取事务启动后被修改，会检查该记录的DB_TRX_ID，并且从撤销日志中检索记录的正确版本

当二级索引记录被标记为删除或被一个新事务更新，覆盖索引（covering index）记录将无法使用。替代从索引结构获取返回值，InnoDB从聚簇索引查找记录

然而，如果index condition pushdown(ICP)优化被启用，并且部分where条件被评估为只使用索引中的列，MySQL服务器仍将这部分where条件推送给会评估使用索引的存储引擎。如果没有匹配的记录，则避免了查找聚簇索引，如果有匹配的记录，即便在被标记为删除的记录中个，InnoDB也会去聚簇索引中找这些记录


InnoDB架构
---

#### 缓冲池（Buffer Pool）
缓冲池在主内存中作为一个可访问InnoDB缓存表和索引数据的区域。缓冲池允许频繁使用的数据可以直接在内存中处理，提高了处理速度。在专业数据库服务器，超过80%的物理内存通常用于InnoDB缓冲池。

为了大批量读操作的效率，缓冲池被分成可能拥有多行的页面（pages）。为了提高缓存管理的效率，缓冲池被实现成一个pages组成的链表（linked list）;很少使用的数据会因老化从缓存中移除，使用了一个变种的LRU算法。

#### 变更缓冲区（Change Buffer）
change buffer 是一个特殊的数据结构，当受影响的页面不在缓冲池中时，缓存对辅助索引页面（secondary index pages）的更改。这些可能由于INSERT,UPDATE,DELETE操作导致的缓冲变更，会在由其他读操作把pages加载到缓冲池时被合并


不像聚簇索引，二级索引通常是非唯一的，并且插入二级索引是按相对随机的顺序。相似的，删除和更新可能影响二级索引页不会相邻地分布在索引数上。当受影响的页面被其他操作读入缓冲池时，合并缓存的更改，避免了从磁盘读入二级索引页所需的大量随机访问I/O

系统大部分空闲时或在慢速关闭期间清除操作（purge operation）会定期将更新的索引页写入磁盘.与每个值立即写入磁盘的情况相比，清除操作可以更有效地为一系列索引值写入磁盘块。

当有许多要更新的二级索引和许多受影响的行时，更改缓冲区合并可能需要几个小时。在此期间，磁盘I / O会增加，这会导致磁盘绑定查询显着减慢.提交事务后，更改缓冲区合并也可能继续发生。实际上，在服务器关闭和重新启动之后，可能会继续发生更改缓冲区合并、

在内存中，更改缓冲区占用InnoDB缓冲池的一部分。在磁盘上，更改缓冲区是系统表空间的一部分，因此索引更改在数据库重新启动时保持缓冲。

innodb_change_buffering配置缓存在change buffer的数据类型

change buffering 不支持包含降序索引列的二级索引，或者主键包含降序索引列。

监控changebuffer，`show engin innodb status\G` 显示存储引擎状态信息，在标题INSERT BUFFER AND ADAPTIVE HASH INDEX下面就是change buffer状态信息


#### 自适应哈希索引（Adaptive Hash Index，AHI）    
自适应哈希索引（AHI）使InnoDB在系统上执行更像内存数据库，具有适当的工作负载组合和分配充足内存的缓冲池，并且不牺牲任何事务功能和可靠性。这个功能由变量innodb_adaptive_hash_index控制。或者在server启动时使用--skip-innodb_adaptive_hash_index关闭

根据观察到的搜索模式，MySQL使用索引键的前缀建立哈希索引。前缀可以是任意长度，并且可能只有B-tree中的部分值出现在哈希索引中。哈希索引是根据需要经常访问的索引页面构建的。

如果一个表完全存在内存中，哈希索引可以通过启用任何元素的直接查找来加速查询，将索引值转换为一种指针。InnoDB有一个机制监控索引查找，如果InnoDB注意到通过建立哈希索引可以优化查询，它便会自动执行。

在某些工作负荷下（workloads），哈希索引查找的加速花费的额外工作大大超过了监控索引查找和维护哈希索引结构。有时候，用于保护访问自适应哈希索引的读/写锁在沉重的工作负荷下成为一个争夺源，例如多个并发join操作。使用like运算符或%通配符的查询同样也无法从AHI中获益。在一些不需要AHI的工作负荷下，关闭它可以减少不必要的性能消耗。因为很难预先预测此功能是否适合特定系统，可以考虑在关闭和开启AHI下，使用实际的工作负荷，跑基准测试（benchmarks）。MySQL 5.6及更高版本中的体系结构更改使得更多工作负载适合于禁用自适应哈希索引，而不是早期版本，尽管默认情况下仍然启用它。

自适应哈希索引搜索系统是分区的。每个索引都绑定到一个特定的分区，每个分区都由一个单独的锁存器保护。分区由innodb_adaptive_hash_index_parts配置选项控制。innodb_adaptive_hash_index_parts选项默认设置为8。最大设置为512。

哈希索引始终基于表上的现有B树索引构建。InnoDB可以在为B树定义的任意长度的key的前缀上构建哈希索引，具体取决于InnoDB针对B树索引观察的搜索模式。哈希索引可以是部分的，仅覆盖经常访问的索引的那些页面

您可以在SHOW ENGINE INNODB STATUS命令的输出的SEMAPHORES部分中监视自适应哈希索引的使用及其使用的争用。如果您看到许多线程在btr0sea.c中创建的RW锁存器上等待，那么禁用自适应哈希索引可能会很有用。


#### Redo Log Buffer（重做日志缓冲区）
重做日志缓冲区是保存要写入重做日志的数据的内存区域。重做日志缓冲区大小由innodb_log_buffer_size配置选项定义。缓冲区会定期刷新到磁盘上的日志文件中。大型重做日志缓冲区可以在事务提交之前运行大型事务，而无需将重做日志写入磁盘。因此，如果您有更新，插入或删除许多行的事务，则使日志缓冲区更大可以节省磁盘I/O.

innodb_flush_log_at_trx_commit选项控制如何将重做日志缓冲区的内容写入日志文件。innodb_flush_log_at_timeout选项控制重做日志刷新频率。

#### System Tablespace（系统表空间）
InnoDB系统表空间包含InnoDB数据字典（InnoDB相关对象的元数据），是doublewrite（双写）缓冲区，更改缓冲区（change buffer）和撤消日志(undo log)的存储区域。系统表空间还包含在系统表空间中创建的任何用户创建的表的表和索引数据.系统表空间被视为共享表空间，因为它由多个表共享。

系统表空间由一个或多个数据文件表示。默认情况下，在MySQL数据目录中创建一个名为ibdata1的系统数据文件。系统数据文件的大小和数量由innodb_data_file_path启动选项控制。

#### Doublewrite Buffer（双写缓冲区）
Doublewrite Buffer是一个位于系统表空间的存储区域，存放了InnoDB在将页面写入数据文件中的正确位置之前写入从InnoDB缓冲池中刷新的页面。
只有在将页面刷新并写入双写缓冲区后，InnoDB才会将页面写入其正确的位置。如果在页面写入（page write）的中间过程发生了操作系统，存储子系统或mysqld进程崩溃，InnoDB稍后可以在崩溃恢复期间从doublewrite缓冲区中找到页面的良好副本。

虽然数据总是写入两次，但是双写缓冲区不需要两倍的I/O开销或两倍的I/O操作。数据作为一个大的顺序块（large sequential chunk）写入doublewrite缓冲区本身，由操作系统进行单个fsync（）调用。

默认开启，由innodb_doublewrite变量控制


#### Undo Logs（撤销日志）
撤消日志是与单个事务关联的撤消日志记录的集合。撤消日志记录包含有关如何撤消事务到聚簇索引记录的最新更改的信息。如果另一个事务需要查看原始数据（作为一致读取操作的一部分），则从撤消日志记录中检索未修改的数据。撤消日志存在于撤消日志段（undo log segments）中，这些日志段包含在回滚段(rollback segments)中。回滚段驻留在撤消撤消表空间和临时表空间中。

全局临时表空间（ibtmp1）和每个撤消表空间分别最多支持128个回滚段。innodb_rollback_segments配置选项定义回滚段的数量。每个回滚段最多支持1023个并发数据修改事务。


#### File-Per-Table Tablespaces
每个file-per-table表空间是一个单表(single-table)表空间，它在自己的数据文件中而不是在系统表空间中创建。启用innodb_file_per_table选项时，将在每个表的文件表空间中创建表。否则，InnoDB表将在系统表空间中创建。每个table-per-table表空间由单个.ibd数据文件表示，该文件默认在数据库目录中创建。

文件每表表空间支持DYNAMIC和COMPRESSED行格式，支持可变长度数据的页外存储（off-page storage）和表压缩等功能

#### General Tablespaces（一般表空间）
共享InnoDB表空间可以使用`CREATE TABLESPACE`语法创建。一般表空间可以在MySQL数据目录之外创建，能够保存多个表，并支持所有行格式的表。
把表添加到一般表空间语法：` CREATE TABLE tbl_name ... TABLESPACE [=] tablespace_name` 或 `ALTER TABLE tbl_name TABLESPACE [=] tablespace_name `

#### Undo Tablespace
Undo tablespace由一个或多个文件包含undo logs组成。undo tablespaces的数量由innodb_undo_tablespaces配置，这个配置已经被弃用。


#### Temporary Tablespace(临时表空间)
用户创建（user-created）临时表和磁盘内部（on-disk internal）临时表都会创建在一个共享的临时表空间。innodb_temp_data_file_path选项定义了临时表空间数据文件的相对路径，名字，大小和属性。如果没有值指定，默认行为是创建一个可自动拓展的数据文件ibtmp1在innodb_data_home_dir选项指定的目录下，大小略大于12MB。

临时表空间在服务器正常关闭或者中止初始化时被移除，在服务器每次启动时重新创建。每次创建时会接收到动态生成的空间ID。如果临时表空间无法创建，服务器就无法启动。服务器意外停止不会移除临时表空间。在这种情况下，数据库管理员可以手动移除临时表空间，或者重启服务器，可以自动移除并重新创建临时表空间。

临时表空间不能存放在裸设备上（raw device）。裸设备(raw device)，也叫裸分区（原始分区），是一种没有经过格式化，不被Unix通过文件系统来读取的特殊块设备文件。由应用程序负责对它进行读写操作。不经过文件系统的缓冲。它是不被操作系统直接管理的设备。这种设备少了操作系统这一层，I/O效率更高。不少数据库都能通过使用裸设备作为存储介质来提高I/O效率。

`mysql> SELECT * FROM INFORMATION_SCHEMA.FILES WHERE TABLESPACE_NAME='innodb_temporary'\G`。查看InnoDB临时表空间的元数据     
`INFOMATION_SCHEMA.INNODB_TEMP_TABLE_INFO`提供在InnoDB实例中的当前活跃的用户自建临时表的元数据。

innodb_rollback_segments配置选项定义全局临时表空间使用的回滚段数。
默认情况下，临时表空间文件是可自动扩展的，在需要容纳磁盘临时表时可自增文件大小。例如一个操作创建了20M的历史表，那么默认是12MB大小的临时表空间可以拓展到可以容纳它的大小。但临时表被删除后，释放的空间可以重新用于新的临时表，但数据文件会保留拓展后的大小。

`innodb_temp_data_file_path=ibtmp1:12M:autoextend:max:500M`，该选项可配置临时表空间的名称，大小，属性，和最大值

临时表空间undo logs保存在全局临时表空间中（global temporary tablespace）(ibtmp1)，用于用户自建临时表和相关对象。它不会被重做日志记录，因为它们不需要再灾难恢复中使用。它们只在服务器运行时在rollback中使用。这种特殊类型的撤销日志通过避免重做日志I/O来提高性能。


#### Redo Log（撤销日志）
重做日志是在崩溃恢复期间用于纠正由未完成事务写入的数据的基于磁盘的数据结构。在正常操作期间，重做日志会编码请求去更改由SQL语句或底层API调用产生的InnoDB表数据。在初始化期间以及接受连接之前，会自动重播在意外关闭之前未完成更新数据文件的修改

默认情况下，重做日志在磁盘上物理表示为一组文件，名为ib_logfile0和ib_logfile1。MySQL以循环方式（circular fashion）写入重做日志文件。循环方式：通常会初始化2个或更多的 ib_logfile 存储 redo log，由参数 innodb_log_files_in_group 确定个数，命名从 ib_logfile0 开始，依次写满 ib_logfile 并顺序重用（in a circular fashion）。如果最后1个
 ib_logfile 被写满，而第一个ib_logfile 中所有记录的事务对数据的变更已经被持久化到磁盘中，将清空并重用之。

#### Group Commit for Redo Log Flushing
与任何其他符合ACID标准的数据库引擎一样，InnoDB在提交事务之前刷新事务的重做日志。InnoDB使用组提交功能将多个此类刷新请求组合在一起，以避免每次提交一次刷新。通过组提交，InnoDB会对日志文件发出一次写操作，以便为几乎同时提交的多个用户事务执行提交操作，从而显着提高吞吐量。


InnoDB 锁和事务模型
---
> 要实现大规模，繁忙或高度可靠的数据库应用程序，从不同的数据库系统移植大量代码，或调整MySQL性能，了解InnoDB锁定和InnoDB事务模型非常重要。

#### 锁

* 共享锁和独占锁（shared and exclusive locks）,又称s锁和x锁
* 意向锁（intention locks）
* 行级锁（record locks）
* 区间锁（gap locks）
* 间隙锁（next-key locks）
* 插入意向锁（inert intertion locks）
* 自增锁 (auto-inc locks)
* 空间索引的谓词锁（predicate locks for spatial indexes）


#### 共享锁和独占锁
共享锁允许拥有该行的共享锁的事务才能进行读取；
独占锁允许拥有该行的独占锁的事务才能进行更新和删除
如果事务t1拥有行r的共享群，此时事务t2过来请求行r 的锁:
1、如果请求的是共享锁，会立马被授权，然后两个事务同时拥有该行的共享锁
2、如果请求的是独占锁，则不会立马被授权
如果事务t1拥有行r 的独占锁，则事务t2请求两种锁都不会被授权，必须等待t1先释放锁

#### Intention Lock (意向锁）
innodb 支持多种粒度的锁，允许行锁和表锁共存。例如，语句`lock  tables ... write`获取指定表的独占锁。为了在多个级别实现多个粒度锁，innodb使用意向锁。意向锁是表级锁，表明一个事务前后在行（row）上会用何种类型的锁（共享或独占）。
意向锁有两种类型:
意向共享锁（intention shared lock, IS）: 表示一个事务尝试在表的各行设置共享锁；
意向独占锁（intention exclusive lock,IX）: 同理；
语句`select...for share`设置IS锁；`select...for update`设置IX锁
意图锁协议如下：
1、在事务可以获取表中某行的共享锁IS之前，它必须首先在表上获取IS锁或更强的锁。
2、在事务可以获取表中某行的独占锁之前，它必须首先获取表上的IX锁。
表级锁定类型兼容性总结在以下表中（顶部）。

授予请求事务一个锁的时候，该锁不能与已存在的锁有冲突，否则只能等待已存在锁释放。锁冲突会导致死锁，并报错。
意向锁不会锁任何东西除非是请求了全表（例如，`lock tables...write`）。意向锁的主要目的是有人正在锁定一行，或者准备锁定一行。
查看意向锁的事务信息:`show engine innodb status`

#### Record Lock（行锁，记录锁）
行锁是对索引加的锁，例如`select c1 from t where c1=10 for update`语句阻止所有其他任何事物对t.c1=10的所有行进行插入，更新和删除操作
行锁总是锁定索引记录，即使表没有定义索引。在这种情况，innodb创建了一个隐藏的聚簇索引，然后用这个索引用于行锁
查看意向锁的事务信息:`show engine innodb status`


#### Gap Lock（间隙锁）
间隙锁定是锁定索引记录之间的间隙，或锁定在第一个或最后一个索引记录之前的间隙上。例如，`SELECT c1 FROM t WHERE c1 BETWEEN 10和20 FOR UPDATE;`语句阻止其他事务将值15插入到列t.c1中，无论列中是否存在任何此类值，因为该范围内所有现有值之间的间隙都被锁定。

间隙可能跨越单个索引值，多个索引值，甚至可能为空。

间隙锁是性能和并发之间权衡的一部分，用于某些事务隔离级别而不是其他级别。

使用唯一索引锁定行以搜索唯一行的语句不需要间隙锁定。 （这不包括搜索条件仅包含多列唯一索引的某些列的情况;在这种情况下，确实会发生间隙锁定。）例如，如果id列具有唯一索引，则以下语句仅使用具有id值100的行的索引记录锁定，其他会话是否在前一个间隙中插入行无关紧要:`SELECT * FROM child WHERE id = 100;`

如果id未设置索引或具有非唯一索引，则该语句会锁定前一个间隙。

这里还值得注意的是，冲突锁可以通过不同的事务保持在间隙上。例如，事务A可以在间隙上保持共享间隙锁定（间隙S锁定），而事务B在同一间隙上保持独占间隙锁定（间隙X锁定）。允许冲突间隙锁定的原因是，如果从索引中清除记录，则必须合并由不同事务保留在记录上的间隙锁定。

InnoDB中的间隙锁是“纯粹的抑制”，这意味着它们的唯一目的是防止其他事务插入间隙。间隙锁可以共存。一个事务占用的间隙锁定不会阻止另一个事务在同一个间隙上进行间隙锁定。共享和独占间隙锁之间没有区别。它们彼此不冲突，它们执行相同的功能。

可以明确禁用间隙锁定。如果将事务隔离级别更改为READ COMMITTED，则会发生这种情况。在这些情况下，对于搜索和索引扫描禁用间隙锁定，并且仅用于外键约束检查和重复键（duplicate-key）检查。

使用READ COMMITTED隔离级别还有其他影响。 MySQL评估WHERE条件后，将释放非匹配行的记录锁。对于UPDATE语句，InnoDB执行“半一致”读取，以便将最新提交的版本返回给MySQL，以便MySQL可以确定该行是否与UPDATE的WHERE条件匹配。

#### next-key Locks
next-key锁是记录锁和间隙锁的组合，既锁范围，又锁记录本身。解决幻读问题。


#### insert intention locks（插入意向锁）
插入意向锁是在行插入之前由INSERT操作设置的一种间隙锁。这个锁表示插入的意图，即插入相同索引间隙的多个事务如果不插入间隙内的相同位置则不需要等待彼此。假设存在值为4和7的索引记录。分别尝试插入值5和6的事务，分别在获取插入行上的排它锁之前用插入意图锁定锁定4和7之间的间隙，但是不会互相阻塞，因为这些行是非冲突的。

#### auto-inc locks （自增锁）
AUTO-INC锁是由插入到具有AUTO_INCREMENT列的表中的事务所采用的特殊表级锁。在最简单的情况下，如果一个事务正在向表中插入值，则任何其他事务必须等待对该表执行自己的插入，以便第一个事务插入的行接收连续的主键值。

innodb_autoinc_lock_mode配置选项控制用于自动增量锁定的算法。它允许您选择如何在可预测的自动增量值序列和插入操作的最大并发之间进行权衡

#### Predicate Locks for Spatial Indexes（空间索引的谓词锁）


InnoDB Transaction Model（inoodb事务模型）
---

> 在InnoDB事务模型中，目标是将多版本数据库的最佳属性与传统的两阶段锁定相结合。InnoDB在行级别执行锁定，默认情况下以Oracle的方式运行查询作为非锁定一致性读取。InnoDB中的锁定信息存储空间有效，因此不需要锁定升级。通常，允许多个用户锁定InnoDB表中的每一行或行的任何随机子集，而不会导致InnoDB内存耗尽。

#### transaction isolation levels（事务隔离级别）
事务隔离是数据库处理的基础之一。隔离级别是在多个事务进行更改并同时执行查询时，对结果的性能和可靠性，一致性和可重现性之间的平衡进行微调的设置。    

InnoDB提供了四种隔离级别：
* READ UNCOMMITTED （未提交读）
* READ COMMITTED （已提交读）
* REPEATEABLE READ （可重复读，默认）
* SERIALIZABLE （序列化）

用户可以使用`SET TRANSACTION`语句更改单个会话或所有后续连接的隔离级别。要为所有连接设置服务器的默认隔离级别，请在命令行或选项文件中使用--transaction-isolation选项

InnoDB使用不同的锁策略支持此处描述的每个事务隔离级别。您可以使用默认的REPEATABLE READ级别强制执行高度一致性，以便对需要保证ACID的关键数据进行操作。或者您可以放松使用READ COMMITTED甚至READ UNCOMMITTED的一致性规则，例如批量报告，其中精确一致性和可重复结果不如最小化锁定开销量重要。SERIALIZABLE强制执行甚至比REPEATABLE READ更严格的规则，主要用于特殊情况，例如XA事务以及并发和死锁的故障排除问题。

#### REPEATEABLE READ（可重复度）
默认隔离级别。同一事务中的一致读取（consistent reads）读取第一次读取建立的快照。这意味着如果在同一事务中发出几个普通（非锁定）SELECT语句，这些SELECT语句也相互一致。   

对于锁定式读取（使用FOR UPDATE或FOR SHARE的SELECT），UPDATE和DELETE语句，锁定取决于语句是使用具有唯一搜索条件的唯一索引还是范围类型搜索条件
* 对于具有唯一搜索条件的唯一索引，InnoDB仅锁定找到的索引记录，而不是之前的间隙。
* 对于其他搜索条件，InnoDB使用间隙锁或next-key锁来锁定扫描的索引范围，以阻止其他会话插入到范围所涵盖的间隙中

#### READ COMMITTED （已提交读）
即使在同一事务中，每个一致的读取也会设置和读取自己的新快照。     
对于锁定读取（使用FOR UPDATE或FOR SHARE的SELECT），UPDATE语句和DELETE语句，InnoDB仅锁定索引记录，而不是它们之前的间隙，因此允许在锁定记录旁边自由插入新记录。间隙锁定仅用于外键约束检查和重复键检查。由于禁用了间隙锁定，因此可能会出现幻像问题（幻读问题），因为其他会话可以在间隙中插入新行。     
READ COMMITTED隔离级别仅支持基于行（row-based）的二进制日志记录。如果对binlog_format = MIXED使用READ COMMITTED，则服务器会自动使用基于行的日志记录。

使用READ COMMITTED会产生额外的影响：
* 于UPDATE或DELETE语句，InnoDB仅为其更新或删除的行保留锁定。MySQL评估WHERE条件后，将释放非匹配行的记录锁。这大大降低了死锁的可能性，但它们仍然可以发生。
* 对于UPDATE语句，如果一行已被锁定，InnoDB执行“半一致”读取，将最新提交的版本返回给MySQL，以便MySQL可以确定该行是否与UPDATE的WHERE条件匹配。如果行匹配（必须更新），MySQL再次读取该行，这次InnoDB将其锁定或等待锁定。

示例：
```
CREATE TABLE t (a INT NOT NULL, b INT) ENGINE = InnoDB;
INSERT INTO t VALUES (1,2),(2,3),(3,2),(4,3),(5,2);
COMMIT;
```
在这种情况下，表没有索引，因此搜索和索引扫描使用隐藏的聚簇索引进行记录锁定而不是索引列。    
假设一个会话使用以下语句执行UPDATE：
```
# Session A
START TRANSACTION;
UPDATE t SET b = 5 WHERE b = 3;
```
假设第二个会话通过执行第一个会话的那些语句来执行UPDATE：
```
# Session B
UPDATE t SET b = 4 WHERE b = 2;
```
当InnoDB执行每个UPDATE时，它首先获取每行的独占锁，然后确定是否修改它。如果InnoDB没有修改行，它会释放锁。否则，InnoDB会保留锁定，直到交易结束。这会影响事务处理，如下所示。     
使用默认的REPEATABLE READ隔离级别时，第一个UPDATE在它读取的每一行上获取一个x锁定，并且不释放任何一个：
```
x-lock(1,2); retain x-lock
x-lock(2,3); update(2,3) to (2,5); retain x-lock
x-lock(3,2); retain x-lock
x-lock(4,3); update(4,3) to (4,5); retain x-lock
x-lock(5,2); retain x-lock
```
第二个UPDATE一旦尝试获取任何锁就会阻塞（因为第一次更新已保留所有行的锁），并且在第一次UPDATE提交或回滚之前不会继续：
```
x-lock(1,2); block and wait for first UPDATE to commit or roll back
```
如果使用READ COMMITTED，则第一个UPDATE在它读取的每一行上获取一个x锁，并释放那些不修改的行：
```
x-lock(1,2); unlock(1,2)
x-lock(2,3); update(2,3) to (2,5); retain x-lock
x-lock(3,2); unlock(3,2)
x-lock(4,3); update(4,3) to (4,5); retain x-lock
x-lock(5,2); unlock(5,2)
```
对于第二个UPDATE，InnoDB执行“半一致”读取，返回它读取到MySQL的每一行的最新提交版本，以便MySQL可以确定该行是否与UPDATE的WHERE条件匹配：
```
x-lock(1,2); update(1,2) to (1,4); retain x-lock
x-lock(2,3); unlock(2,3)
x-lock(3,2); update(3,2) to (3,4); retain x-lock
x-lock(4,3); unlock(4,3)
x-lock(5,2); update(5,2) to (5,4); retain x-lock
```
但是，如果WHERE条件包含索引列，并且InnoDB使用该索引，则在获取和保留记录锁时仅考虑索引列。在下面的示例中，第一个UPDATE在每个行上获取并保留一个x锁定，其中b = 2.第二个UPDATE在尝试获取相同记录的x锁时阻塞，因为它还使用在列b上定义的索引。
```

CREATE TABLE t (a INT NOT NULL, b INT, c INT, INDEX (b)) ENGINE = InnoDB;
INSERT INTO t VALUES (1,2,3),(2,2,4);
COMMIT;

# Session A
START TRANSACTION;
UPDATE t SET b = 3 WHERE b = 2 AND c = 3;

# Session B
UPDATE t SET b = 4 WHERE b = 2 AND c = 4;
```

#### READ UNCOMMITTED
SELECT语句以非锁定方式执行，但可能会使用行的早期版本。因此，使用此隔离级别，读取的数据不一致，这也称为脏读。否则，此隔离级别与READ COMMITTED类似。

#### SERIALIZABLE
这个级别就像REPEATABLE READ，但InnoDB隐式地将所有普通SELECT语句转换为`SELECT ... FOR SHARE`,如果是禁用了autocommit。如果启用了自动提交，则SELECT是其自己的事务。因此，已知它是只读的，并且如果作为一致（非锁定）读取执行则可以序列化，并且不需要阻止其他事务。（要强制普通SELECT阻止其他事务已修改所选行，请禁用autocommit。）