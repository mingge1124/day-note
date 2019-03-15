nginx ֧�ֵļ��ָ��ؾ����㷨
1. round-robin����ѯ�����㷨�� Ĭ���㷨
2. leaset-connected���������ӣ���һ������ָ�����ٻ�Ծ��������active connections���ķ�����
3. ip-hash  a hash-function is used to determine what server should be selected for the next request (based on the client��s IP address) ���ڿͻ��˵�ip��ַ���ù�ϣ����������̨������������һ������

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
nginxʵ�ֵķ��������������HTTP,HTTPS,FastCGI,uwsgi,SCGI,memcached��gRPC�ĸ��ؾ��⣬���Էֱ�ʹ��proxy_pass,fastcgi_pass,uwsgi_pass,scgi_pass,memcached_pass,grpc_passָ��������

Least connected load balancing
---
��ĳЩ������Ҫ����ʱ�������ɵ�����£���С�����������ƽ�ؿ���Ӧ�ó���ʵ���ϵĸ��ء�ʹ�ô��㷨ʱ��nginx��������ʹ��æ��Ӧ�ó���������������󣬽�������ַ�����̫��æ�ķ�������
`least_conn`ָ����������ʹ��Least connected���㷨�������ؾ���
```
upstream myapp1 {
	least_conn;
        server srv1.example.com;
        server srv2.example.com;
        server srv3.example.com;
}
```

Session persistence �Ự�־���
---
ʹ��round-robin����least-connected�㷨����ͬ�Ŀͻ���������ܻ�ַ�����ͬ�ķ�������û�취��֤��ͬ�Ŀͻ���һֱ������ͬ�ķ���ˡ���ʹ��ip-hash�㷨�Ϳ���������
ip-hash�㷨���ͻ��˵�ip��ַ����Ϊ��ϣֵ��hashing key�������ӷ������飨server group��ѡ���ĸ�������������ͻ��˵��������ַ���ȷ����ͬ�Ŀͻ�������������ͬ�ķ���ˣ����Ǹ÷�����ǲ����õġ�
`ip_hash`ָ����������ʹ��Least connected���㷨�������ؾ���
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
�㷨�����бף�����ͨ������Ȩ������Ԥ���ؾ���    
`weight`����
```
upstream myapp1 {
        server srv1.example.com weight=3;
        server srv2.example.com;
        server srv3.example.com;
}
```
��������ã�ÿ5��������������ͻ���3������srv1��������srv2��srv3��һ��


Health checks �������
---
�������ʵ�ְ����˱����ķ�����������顣�������������Ӧ���ݴ���error��nginx����Ϊ���������ˣ�����һ��ʱ���ڱ���ѡ��÷�������������   
`max_fails`���� �����������ɹ����ӷ������Ĵ�����Ĭ��ֵ��1������Ϊ0ʱ���رս�����顣       
`fail_timeout`���� ���ñ�ʶʧ�ܵ�ʱ��������ʱ����nginx������̽��������Ƿ���á�     
```
upstream myapp1 {
        server srv1.example.com weight=3 max_fails=2 fail_timeout=30s;
        server srv2.example.com weight=1 max_fails=2 fail_timeout=30s;
        server srv3.example.com weight=1 max_fails=2 fail_timeout=30s;
}
```







