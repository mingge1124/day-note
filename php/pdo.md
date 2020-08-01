### pdo(php data object)
作用：为PHP定义了一套轻量级，一致性的接口用于访问数据库。提供了一个数据访问抽象层，这意味着，不管使用哪种数据库，都可以用相同的函数（方法）来查询和获取数据。
注意：是data-access abstraction layer，不是database abstraction
过程：php->pdo->pdo_db_driver(pdo_mysql, pdo_odbc等驱动，实现了pdo的接口)->mysqlnd(全称mysql native driver，负责编译driver的数据，然后与服务端通讯，等同于替代了mysql client端)->db server

`new pdo($dsn, $user, $pass, $options)` 与数据库成功链接后，返回pdo对象，connection 会在pdo对象生命周期内都保持active.
如果要主动关闭connection，必须要保证pdo对象包含所有引用都要删除，赋值null给保存该对象的变量即可做到。如果不手动设置，那么
connection会在php脚本执行结束后被关闭

* 连接数据库失败会抛出异常，如果不设置异常处理，zend engine会关闭当前脚本并显示堆栈信息，里面会包含完整的数据连接信息，包括用户名和密码，所以一定要catch该异常
  
### mysqlnd (mysql native driver)
1 各类 mysql server 和各类 clients APIs 区分在于buffered result set 和 unbuffered result set，unbuffered result set是指server一行一行地传送数据给client， buffered则是server是一次性把完整的数据传给client

2 mysqlnd 使用 php stream 与 mysql server 进行网络通讯，mysql server 首先发送结果到 php stream buffers (缓存区)，然后传给 mysqlnd 的 result buffer(make of zvals)，最后mysqlnd把结果传给 php 变量，
  这一步会影响内存消耗，特别是在使用buffered result sets 时尤为明显