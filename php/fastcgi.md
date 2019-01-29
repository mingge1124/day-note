FASTCGI
---

#### 1、概念
Instead of creating a new process for each request, FastCGI uses persistent processes to handle a series of requests. These processes are owned by the FastCGI server, not the web server. [1]
使用持久性进程处理一系列请求，这些进程由fastcgi服务器处理，而不是web服务器

To service an incoming request, the web server sends environment information and the page request itself to a FastCGI process over either a Unix domain socket, a named pipe or a TCP connection. Responses are returned from the process to the web server over the same connection, and the web server subsequently delivers that response to the end-user. The connection may be closed at the end of a response, but both the web server and the FastCGI service processes persist.[2]
处理一个进来的请求，web服务器会通过Unix domain socket或命名管道（name pipe）或tcp连接发送环境信息和页面请求本身给fastcgi 进程。响应内容会从该进程在相同连接上返回给web服务器，随后web服务器传送给终端用户。当前连接可能会在响应结束时关闭，但是web服务器和fastcgi 服务进程依然保留

Each individual FastCGI process can handle many requests over its lifetime, thereby avoiding the overhead of per-request process creation and termination. Processing of multiple requests simultaneously can be achieved in several ways: by using a single connection with internal multiplexing (i.e. multiple requests over a single connection); by using multiple connections; or by a combination of these techniques. Multiple FastCGI servers can be configured, increasing stability and scalability.
每个独立fastcgi进程可以在它生命周期内处理多个请求，因此避免了单个请求进程创建和销毁的开销。有多种方式能实现同时处理多个请求：使用内部多路复用的单个连接（单个连接处理多个请求）；使用多个连接；或者是用以上技术的组合。可以通过配置多个FastCGI servers来增加稳定性和可扩展性。


