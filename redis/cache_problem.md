1. 缓存失效
问题描述：　　
引起这个原因的主要因素是高并发下，我们一般设定一个缓存的过期时间时，可能有一些会设置5分钟啊，10分钟这些；并发很高时可能会出在某一个时间同时生成了很多的缓存，并且过期时间在同一时刻，这个时候就可能引发——当过期时间到后，这些缓存同时失效，请求全部转发到DB，DB可能会压力过重。

处理方法：
一个简单方案就是将缓存失效时间分散开，不要所以缓存时间长度都设置成5分钟或者10分钟；比如我们可以在原有的失效时间基础上增加一个随机值，比如1-5分钟随机，这样每一个缓存的过期时间的重复率就会降低，就很难引发集体失效的事件。
缓存失效时产生的雪崩效应，将所有请求全部放在数据库上，这样很容易就达到数据库的瓶颈，导致服务无法正常提供。尽量避免这种场景的发生。

2. 缓存穿透
问题描述：　　
指查询一个一定不存在的数据，由于缓存是不命中时被动写的，并且出于容错考虑，如果从存储层查不到数据则不写入缓存，这将导致这个不存在的数据每次请求都要到存储层去查询，失去了缓存的意义。
当在流量较大时，出现这样的情况，一直请求DB，很容易导致服务挂掉。

处理方法：
方法1.在封装的缓存SET和GET部分增加个步骤，如果查询一个KEY不存在，就已这个KEY为前缀设定一个标识KEY；以后再查询该KEY的时候，先查询标识KEY，如果标识KEY存在，就返回一个协定好的非false或者NULL值，然后APP做相应的处理，这样缓存层就不会被穿透。当然这个验证KEY的失效时间不能太长。
方法2.如果一个查询返回的数据为空（不管是数据不存在，还是系统故障），我们仍然把这个空结果进行缓存，但它的过期时间会很短，一般只有几分钟。
方法3.采用布隆过滤器，将所有可能存在的数据哈希到一个足够大的bitmap中，一个一定不存在的数据会被这个bitmap拦截掉，从而避免了对底层存储系统的查询压力。

3. 缓存并发
问题描述：
当网站并发访问高，一个缓存如果失效，可能出现多个进程同时查询DB，同时设置缓存的情况，如果并发确实很大，这也可能造成DB压力过大，还有缓存频繁更新的问题。

处理方法：
对缓存查询加锁，如果KEY不存在，就加锁，然后查DB入缓存，然后解锁；其他进程如果发现有锁就等待，然后等解锁后返回数据或者进入DB查询。




缓存雪崩：redis服务器挂掉导致请求大量涌至数据库；
缓存穿透：大量缓存中不存在的请求key访问直接落到数据库，一般是恶意攻击；
缓存击穿：热点key在请求高峰失效，瞬间大量请求落到数据库