### 安装php拓展问题

### 核心步骤
```
1 拓展包目录下执行phpize生成configure
2 ./configure --with-php-config=/path/php-config ...  
3 make && make install
```


#### libzip
* libzip.so.5 not found
【ldd命令】ldd是list, dynamic, dependencies的缩写， 意思是， 列出动态库依赖关系
以下是解决方案
ldd /你的so目录/zip.so
linux-vdso.so.1 => (0x00007ffc82d4c000)【正常加载】
libzip.so.5 => not found【错误，找不到】
libc.so.6 => /lib64/libc.so.6 (0x00007f61063c9000)【正常加载】
/lib64/ld-linux-x86-64.so.2 (0x00007f61069a5000)【正常加载】


你会发现：你的libzip.so.5在系统里面找不到。
意思就是libzip库，你没有加载到linux系统里面。
加载方式如下：
cp /etc/ld.so.conf.d/local.conf /etc/ld.so.conf.d/libzip.conf
【local.conf是空白文件】
vim /etc/ld.so.conf.d/libzip.conf
【编辑这个.conf文件，输入libzip.so.5路径】
/usr/local/libzip/lib64
【然后:wq保存】
ldconfig
【重启环境变量】
再次尝试：ldd /你的so目录/zip.so


### pdo_mysql
https://www.zhile.name/110.html

解决centos安装mariadb
https://www.jianshu.com/p/2111a87ac613