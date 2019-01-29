NFS服务器端其实是随机选择端口来进行数据传输

NFS服务器时通过远程过程调用（remote procedure call 简称RPC）协议/服务来实现的

RPC服务会统一管理NFS的端口，客户端和服务端通过RPC来先沟通NFS使用了哪些端口，之后再利用这些端口（小于1024）来进行数据的传输。

NFS服务端需要先启动rpc，再启动NFS，这样NFS才能够到RPC去注册端口信息

修改NFS配置文档后，是不需要重启NFS的，直接在命令执行/etc/init.d/nfs  reload或exportfs –rv即可使修改的/etc/exports生效


客户端NFS和服务端NFS通讯过程

1）首先服务器端启动RPC服务，并开启111端口

2）启动NFS服务，并向RPC注册端口信息

3）客户端启动RPC（portmap服务），向服务端的RPC(portmap)服务请求服务端的NFS端口

4）服务端的RPC(portmap)服务反馈NFS端口信息给客户端。

5）客户端通过获取的NFS端口来建立和服务端的NFS连接并进行数据的传输。


/var/lib/nfs/etab   查看nfs共享了哪些目录
/var/lib/nfs/rmtab  看到共享目录被挂载的情况