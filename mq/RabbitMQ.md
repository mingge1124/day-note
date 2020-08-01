RabbitMQ

确保即使消费者崩溃，任务也不会丢失，但是如果rabbitmq server关闭，任务仍会丢失

rabbitmq退出或崩溃时，它会丢失所有queues和messages。需要标记queue和message


queue持久化，在queue_declare时声明

message持久化  在初始化amqpmessage时声明，仍然不能保证消息的持久化，在设置标志位告诉rabbitmq保存message到硬盘时
，中间仍然有一段很短的时间差。此外，rabbitmq不对每一个消息执行fsync(2),他可能只是保存到缓存中，而不是写入磁盘

#### 概念

Broker： 简单来说就是消息队列服务器实体

connection 生产者和消费通过tcp连接到rabbitmq 服务器

Exchange： 消息交换机，它指定消息按什么规则，路由到哪个队列

Queue： 消息队列载体，每个消息都会被投入到一个或多个队列

Binding： 绑定，它的作用就是把exchange和queue按照路由规则绑定起来

Routing Key： 路由关键字，exchange根据这个关键字进行消息投递

VHost： 虚拟主机，一个broker里可以开设多个vhost，用作不同用户的权限分离。

Producer： 消息生产者，就是投递消息的程序

Consumer： 消息消费者，就是接受消息的程序

Channel： 消息通道，在客户端的每个连接里，可建立多个channel，每个channel代表一个会话任务

message 传递的消息包


### exchange type
1 direct  
消费者绑定exchange到queue的routing key，生产者要发送消息时需要带上routing key给exchange，才能到达对应的queue

2 topic 
模糊匹配的direct，`*`表示占位一个单词，`#`表示占位多个单词，单词用`.`连接
如queue1绑定routing key为`*.*.apple`，queue2绑定routing key为`fruit.#`，那生产者带的routing key为`test.test.apple`时会分配到queue1，为`fruit.test.test.test`时，会分配到queue2,
为`fruit.test.apple`时，会分配到queue1和queue2

3 headers  
无需绑定routing key，提取http头部的指定的一组键值对比较

4 fanout:  
无需绑定routing key，会分配给所有已知的queue


#### 问题
1 消息推送基本流程

2消息确认：
消费者消费成功需要发送ack给server，server才认为该task成功，不正常成功（如何判断不成功？超时？），该task将重新分配
ACK的机制可以起到限流的作用（Benefitto throttling）：在Consumer处理完成数据后发送ACK，甚至在额外的延时后发送ACK，将有效的balance Consumer的load。

3 崩溃数据的保存
* queue的持久化
* message的持久化

4 task 分配公平性


#### 消息队列常见问题
参考链接：https://blog.csdn.net/Iperishing/article/details/86674649?utm_medium=distribute.pc_relevant.none-task-blog-BlogCommendFromMachineLearnPai2-1.nonecase&depth_1-utm_source=distribute.pc_relevant.none-task-blog-BlogCommendFromMachineLearnPai2-1.nonecase

1 重复消费
① 如果写入数据库就先根据主键查一下，如果已存在就不插入，update即可。
② 如果是写入Redis，那就没问题，反正每次都是set，天然幂等性。
③ 生产者每次写入消息可以加入一个全局唯一ID，类似订单ID，当消费时，现根据ID去Redis查一下之前是否被消费过，如果被消费过就不处理。
④ 基于数据库的唯一键来保证重复数据不会重复插入多条。因为有唯一键约束，再次插入重复数据只会报错。


2 消息执行顺序：sql的增删改顺序入queue，多个consumer取消息，不能确定sql是否按顺序执行
① 一个queue只分配一个消费者，这问题有点鸡肋？假如是多个consumer取消息+额外的排序执行能达到有序，那为什么不如直接一个消费者顺序执行

3 事务消息


4 高可用

5 消息丢失
confirm机制

6 挤压场景


#### 文档
amqp091协议：https://www.rabbitmq.com/amqp-0-9-1-reference.html

