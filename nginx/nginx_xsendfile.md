Nginx_XSendFile
---

> [原文](https://www.nginx.com/resources/wiki/start/topics/examples/xsendfile/)

该功能的标准格式文档：[X-Accel](https://www.nginx.com/resources/wiki/start/topics/examples/x-accel/)

一个静态文件的传输由一个应用的头部信息（application header）来决定是 X-Sendfile 被认知的功能。
Lighttpd 拥有此类[功能](http://www.lighttpd.net/) , Apache2 同类功能[模块](https://tn123.org/mod_xsendfile/)。

NGINX 也有此功能，但在实现上有一点不同。在 NINGX 中，这个功能称为 X-Accel-Redirect。
主要有两点不同：     
1. 头部信息（header）必须包含 URI。     
2. location 需要定义为 internal 类型；用于阻止客户端（client）直接访问该 URI。     

例子:
```
location /protected/ {
 internal;
 root   /some/path;
}
```

如果应用添加一个头部信息 X-Accel_Redirect 指向 /protected/iso.img;      
例子：    
```
X-Accel-Redirect: /protected/iso.img;
```
PHP例子：
```
$path = '/protected/iso.img';
header('X-Accel-Redirect: ' . $path);
```
然后 NGINX 将会作用于文件 /some/path/protected/iso.img，注意 root 路径和 internal 重定向路径是级联的。

如果你想传输 /some/path/iso.img 文件，你可以这样配置：
```
location /protected/ {
  internal;
  alias   /some/path/; # 注意末尾的反斜杠
}
```

注意: 以下的 HTTP headers 不会被 NGINX 修改：
```
Content-Type
Content-Disposition
Accept-Ranges
Set-Cookie
Cache-Control
Expires
```
如果以上的头部信息未设置，那么它们将会由重定向的响应设置（set by the redirected response）。

应用还可以在过程中添加一些控制，在 X-Accel-Redirect 设置之前发送以下头部信息。
```
X-Accel-Limit-Rate: 1024
X-Accel-Buffering: yes|no
X-Accel-Charset: utf-8
```