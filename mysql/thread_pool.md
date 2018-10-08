线程池解决每个连接使用单个线程模型的几个问题
----     
1、过多的线程堆栈导致CPU缓存在高并行执行工作负载中变得几乎无效。线程池通过促进线程堆栈复用来最小化CPU缓存占用空间。  

2、过多并行执行的线程，上下文切换开销会很高。对于操作系统调度器来说也是一个挑战。线程池通过控制活动线程的数量，以使MySQL服务器内的并行性保持在它可以处理的水平，并且适用于MySQL正在执行的服务器主机   

3、过多并行执行的事务会增加资源竞争。在InnoDB中，这会花费时间用于保持中央互斥（理解为需要一直保持抢夺资源拥有权的状态）。线程池会控制事务开始的时间已保证不会有太多事务并行发生    

结构
----
1、线程池由n个thread groups组成，每个thread groups管理n个client connections。每当connections建立时，线程池便通过round-robin（轮询调度）方法来分配给相应的thread group。

2、thread groups 个数由系统变量 thread_pool_size 决定，默认值为16。每个group最大线程数为4096，有些系统是4095，因为有一个线程作为内部使用。

3、线程池分离了connections和threads，让两者之间没有固定的关系。这就解决了单个连接单个线程模型（one thread per connection）的一个线程需要处理来自一个connection的所有sql 语句。

4、线程池尝试确保在任何时候每个group最多只有一个线程在运行，但有时候允许更多线程临时执行能获得更好的性能

线程池执行算法
---
1、每个thread group有一个监听线程（listener thread），监听分配给该group的connection传过来的语句，每当接收到一个语句，thread group 将可能执行以下两种操作之一：立即执行 / 排队    
* 立即执行出现在只接收到一条依据或者没有语句在排队，或没有语句正在执行
* 排队出现在语句不能立即执行的情况下    

2、如果可以立即执行，那将会由该监听线程负责执行该语句。（这意味着当前该group会临时没有线程在监听。）如果语句迅速执行完成（finished quikly），执行线程便会返回监听线程，否则，线程池会认为语句停滞（stalled），然后开启另一个线程作为监听线程。为了确保thread group 不会因为停滞的语句锁住，线程池会开启一个后台线程（background thread）负责监控thead group的状态。    
* 监听线程-》执行线程-》监听线程 这种方式可以在线程并发数较少的情况下获得最大的效率
* 当线程池开启时，它会在每个thread group开启一个监听线程，加上后台线程，其余的线程创建在需要执行语句时。

3、系统变量thread_pool_stall_limit 决定finished quikly。默认60ms，最大6s。此参数配置可使你能够获得适合服务器工作负载的平衡。设置小值可以让线程启动更快，也可更好地避免死锁。设置大值对于长时间执行的语句更有用，可以避免当前语句执行时，开启很多新语句执行。

4、线程池重点在限制并发短期执行（short-running）语句的数量，确保每个thread group 永远只有一个short-running语句，但是可能会有多条long-running语句。long-runnig 语句是不能阻止其他语句的执行的，因为需要等待的时间无法估量。例如，一个主服务器会有一个线程一直在传送二进制日志给从服务器。

5、语句在遇到磁盘I/O操作或者用户级别锁（row lock or table lock）。锁会造成thread group 变成unused状态，线程池接收到相关回调信息就可以立即在该group开启一个新线程来执行其他的语句。当被锁线程返回后，线程池又可以立即启用它。

6、线程池有两种队列（queue）：高优先级（high_priority）和 低优先级 （low-priority）。一个事务中的第一条语句会先放到低优先级队列中，假如事务开始执行（语句被开始执行），那该事务此后的语句都会被放到高优先级队列中，否则，还是会被放到低优先级队列。队列调度受系统变量thread_pool_high_priority_connection影响，它可以使一个会话（session）中的所有已排队语句都放到高优先级队列中。   
* 非事务型语句或事务型存储引擎（engine）中，autocommit 被开启，所有语句都被认为低优先级。以此，操作innodb表的语句会比操作myisam表的语句优先级更高，除非 开启了 autocommit。    
* 当语句处于低优先级队列太长时间，线程池会把它移到高优先级队列。时间由 thread_pool_prio_kickup_timer 控制。对于每个thread group，一条语句最多10毫秒，或者100条语句最多1s。

7、线程池复用最活跃的线程让CPU caches获得更好的使用。

8、While a thread executes a statement from a user connection, Performance Schema instrumentation accounts thread activity to the user connection. Otherwise, Performance Schema accounts activity to the thread pool.

造成一个thread group有多个线程在执行语句的条件
----
1、一个线程执行语句达到时间限制，thread group允许开启另一个线程执行另外的语句，即使第一个线程仍在执行。

2、一个线程执行语句时被锁了，报告给线程池后，thread group允许开启另一个线程执行另外的语句

3、一个线程执行语句时被锁了，没有报告它被锁了，因为锁没有被线程池callback代码检测到，这时，线程仍在跑，直到超时被认为停滞了，group允许开启另一个线程执行另外的语句。


线程池被设计成在越来越多的connections下也是可拓展的，还可避免因限制活动执行语句的数量而引起的死锁。 It is important that threads that do not report back to the thread pool do not prevent other statements from executing and thus cause the thread pool to become deadlocked。Examples of such statements follow: 

* long-running语句，会导致所有资源只被少数语句使用，同时阻止了其他语句访问服务器
* 二进制日志转储线程（binary log dump threads）读取二进制日志并发送给从库。这类语句会执行很长时间，并且不能阻止其他语句执行
* Mysql Server 或一个存储引擎 没有将 行锁，表锁，sleep，其他锁住的activity等 reported back 给线程池

最大线程数 = max_connections + thread_pool_size。当所有connections在执行并且每个group开启了一个额外的线程用于监听更多的语句，就会达到了最大线程数。实际上不怎么会出现，理论上是可以的。


合理设置线程池系统变量，提高性能
----
1、thread_pool_size，最重要，只能在mysql服务启动前设置。    
* 主要存储引擎为InnoDB时，最优值在16到36之间，更常用的最佳值是24到36，暂时没有什么情况需要超过36，有些特殊情况会存在最佳值比16小。但在DBT2和Sysbench(测试工具)的环境下，最佳值一般设置超过36，对于写密集型的环境，最佳值有时会设置为更低。

* 主要存储引擎为MyISAM时，值应该设置得相当低。一般在4到8之间。值设置过高可能会有轻微的性能问题，但影响不大。


2、thread_pool_stall_limit，对于处理锁和long-running语句很重要，可以在runtime中设置。值设置需要看实际情况，假如百分之99.99的语句都在100ms以内，那值应该设置为10（代表100ms）。
* 假设tp_thread_group_stats表可以访问，你可以用一下语句计算百分比：    
```
SELECT SUM(STALLED_QUERIES_EXECUTED) / SUM(QUERIES_EXECUTED)
FROM performance_schema.tp_thread_group_stats;
```
百分比需要尽可能地低，为了减少语句阻塞的可能性，需要增大该变量值