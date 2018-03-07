## NGINX_X-Accel
> [原文](https://www.nginx.com/resources/wiki/start/topics/examples/x-accel/)

概述：
---
X-accel 允许内部重定向到由后端返回的头部决定的位置。

它允许你在后端处理认证，日志，或其他你需要处理的，然后nginx负责提供内容重定向后的位置给最终用户，因而可以解放后端去处理其他请求。
这个功能被公共熟知的有 X-Sendfile。

这个功能和标准的 NGINX 模块有点不同，它不依赖于指令，而是以一种特殊的方式处理来自上层的头部信息。它的工作方式是：你发送一个头部信息 x-accel.redirect 附带一个 URI。NGINX 将会针对 locations 匹配这个 URI，如果是一个正常的请求。最后nginx会提供由匹配的默认 root 路径 + 头部信息传送的 URI 组成的位置。

配置实例，注意 alias 和 root 的差异。

URI 为 /protected_files/myfile.tar.gz
```
location /protected_files {
  internal;
  alias /var/www/files;
}
```
将会提供 /var/www/files/myfile.tar.gz

URI 为 /protected_files/myfile.tar.gz
```
location /protected_files {
  internal;
  root /var/www;
}
```
将会提供 /var/www/protected_files/myfile.tar.gz

你也可以代理到另一个服务器
```
location /protected_files {
  internal;
  proxy_pass http://127.0.0.2;
}
```

特殊头部：
X-Accel-Redirect
语法：X-Accel-Redirect uri
默认：X-Accel-Redirect void
功能：设置 NGINX 需要操作的 URI

X-Accel-Buffering
语法：X-Accel-Buffering [yes|no]
默认：X-Accel-Buffering yes
功能：为本次连接设置代理缓冲。设置为 no , 将允许无缓冲的响应适用于 [Comet](https://en.wikipedia.org/wiki/Comet_(programming)) 和 http 流应用程序； 设置为 yes，将允许缓存该响应.

X-Accel-Charset
语法：X-Accel-Charset charset
默认：X-Accel-Charset utf-8
功能：设置文件字符编码。

X-Accel-Expires
语法：X-Accel-Expires [off|seconds]
默认：X-Accel-Expires off
功能：设置何时使内部nginx缓存中的文件到期（如果使用的话）。

X-Accel-Limit-Rate
语法：X-Accel-Limit-Rate bytes [bytes|off]
默认：X-Accel-Limit-Rate off
功能：设置单个请求的速率限制，off 代表不限制。