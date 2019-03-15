nginx 支持的几种负载均衡算法
1. round-robin（轮询调度算法） 默认算法
2. leaset-connected（最少连接）下一个请求被指向最少活跃连接数（active connections）的服务器
3. ip-hash  a hash-function is used to determine what server should be selected for the next request (based on the client’s IP address) 基于客户端的ip地址，用哈希函数决定哪台服务器处理下一个请求

round-robin load balancing
---
```
http {
    upstream myapp1 {
        server srv1.example.com;
        server srv2.example.com;
        server srv3.example.com;
    }
    server {
        listen 80;

        location / {
            proxy_pass http://myapp1;
        }
    }
}
```
nginx实现的反向代理包含了针对HTTP,HTTPS,FastCGI,uwsgi,SCGI,memcached和gRPC的负载均衡，可以分别使用proxy_pass,fastcgi_pass,uwsgi_pass,scgi_pass,memcached_pass,grpc_pass指令来配置

Least connected load balancing
---
在某些请求需要更长时间才能完成的情况下，最小连接允许更公平地控制应用程序实例上的负载。使用此算法时，nginx将尽量不使繁忙的应用程序服务器超载请求，将新请求分发给不太繁忙的服务器。
`least_conn`指令用于声明使用Least connected的算法来处理负载均衡
```
upstream myapp1 {
	least_conn;
        server srv1.example.com;
        server srv2.example.com;
        server srv3.example.com;
}
```

Session persistence 会话持久性
---
使用round-robin或者least-connected算法，不同的客户端请求可能会分发到不同的服务器，没办法保证相同的客户端一直连接相同的服务端。但使用ip-hash算法就可以做到。
ip-hash算法：客户端的ip地址会作为哈希值（hashing key）决定从服务器组（server group）选择哪个服务器来处理客户端的请求。这种方法确保相同的客户端总是连接相同的服务端，除非该服务端是不可用的。
`ip_hash`指令用于声明使用Least connected的算法来处理负载均衡
```
upstream myapp1 {
    ip_hash;
    server srv1.example.com;
    server srv2.example.com;
    server srv3.example.com;
}
```

Weight loading balance
--
算法有利有弊，可以通过设置权重来干预负载均衡    
`weight`参数
```
upstream myapp1 {
        server srv1.example.com weight=3;
        server srv2.example.com;
        server srv3.example.com;
}
```
上面的配置，每5个新请求进来，就会有3个分配srv1服务器，srv2，srv3各一个


Health checks 健康检查
---
反向代理实现包含了被动的服务器健康检查。假如服务器的响应内容存在error，nginx会认为服务器坏了，会在一段时间内避免选择该服务器处理请求。   
`max_fails`参数 设置连续不成功连接服务器的次数，默认值是1；设置为0时，关闭健康检查。       
`fail_timeout`参数 设置标识失败的时长，过了时长，nginx会优雅探测服务器是否可用。     
```
upstream myapp1 {
        server srv1.example.com weight=3 max_fails=2 fail_timeout=30s;
        server srv2.example.com weight=1 max_fails=2 fail_timeout=30s;
        server srv3.example.com weight=1 max_fails=2 fail_timeout=30s;
}
```







