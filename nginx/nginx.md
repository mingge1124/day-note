服务启动，停止，重新加载配置文件
配置结构
提供静态内容
配置一个代理服务器
如何如fastcgi应用交互

nginx有一个主进程和多个工作进程，
主进程负责读取和评估配置文件，还有管理工作进程
工作进程负责处理请求
nginx 使用基于事件模型（event-based model）和依赖于操作系统的机制来有效地在工作进程中分配请求
工作进程的数量可以在配置文件中定义，可以是在配置中固定，也可以自动调整为可用的cpu核数

### 服务启动，停止，重新加载配置文件
`nginx -s signal`
signal：
* stop  //直接关闭
* quit  //平滑关闭 ，平滑的意思是会等待工作进程完成当前请求再关闭
* reload  //重新加载配置文件，每次更改需要reload才能生效
* reopen //重新打开日志文件

主进程接收到relaod信号重新加载配置文件时，它会先去检查新配置文件的语法正确性，尝试应用新的配置，
如果返回success，主进程会开启新的工作进程，并发消息给老的工作进程，请求它们关闭，反之返回失败，
主进程就会回滚，然后继续使用旧的配置。老的工作进程收到关闭命令时，会停止接收新连接，然后继续完成
当前请求知道所有请求都完成，之后，老的工作进程关闭。

kill命令也可以通过进程id直接发送信号给nginx进程，例如nginx主进程id为1628，存放在nginx.pid文件里
执行`kill -s QUIT 1628` 平滑关闭nginx


### 配置文件结构
1 简单指令
分号结尾
`access_log  logs/access.log  main;`

2 块指令
events{}
http{
	server{
		location{
		}
	}
}


常用配置：
1 `worker_processes 2;` 设置工作进程数

2 `worker_cpu_affinity cpumask ...;` 绑定工作进程到cpu集合，每个cpu集对应到位掩码
```
worker_processes 2;
worker_cput_affinity 0101 1010
```
进程1绑定到CPU0/CPU2，进程2绑定到CPU1/CPU3


### 配置代理服务器
```
server {
    listen 8080;
    root /data/up1;

    location / {
    }
}

server {
    location / {
        proxy_pass http://localhost:8080/;
    }

    location ~ \.(gif|jpg|png)$ {
        root /data/images;
    }
}
```
更多指令：http://nginx.org/en/docs/http/ngx_http_proxy_module.html

### 与fastcgi交互



### 负载均衡
```
http {
	round-robin; //default ,轮训，适用于服务器配置都一致的情况
	least-connected; //最少连接
	iphash;  //相同ip通过hash计算映射到指定的机子，解决session共享问题，一般用户态不要存在业务服务器，使用redis存放
    upstream myapp1 {
        server srv1.example.com;
        server srv2.example.com;
        server srv3.example.com;
        server srv4.example.com weight=3; //设置服务器权重，根据不同的服务器配置不同的权重
    }

    server {
        listen 80;

        location / {
            proxy_pass http://myapp1;
        }
    }
}
```

### 配置HTTPS



### 内置变量
$document_root 项目路径，等同过于root指令的值
$fastcgi_script_name 等同于请求的URI, 如/index.php




















































