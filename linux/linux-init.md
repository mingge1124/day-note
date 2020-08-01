### linux初始化

#### 配置dns
1 查看当前网络连接
`nmcli connection show`

2 设置DNS服务器
`nmcli con mod enp0s3 ipv4.dns  "114.114.114.114 8.8.8.8"`

3 使DNS配置生效
`nmcli con up enp0s3`

4 查看/etc/resolv.conf文件，看看是否有DNS服务器的配置
`cat /etc/resolv.conf`

5 看是否ping得通

问题:centos7重启后dns解析失效
解决：https://www.linuxidc.com/Linux/2017-07/145844.htm

#### yum换源
1 下载阿里云源
`curl -o /etc/yum.repos.d/CentOS-Base.repo http://mirrors.aliyun.com/repo/Centos-7.repo`

2 生成缓存
`yum makecache`

#### 基本命令安装
```
yum install mlocate wget vim lrzsz
```

#### 一键安装lnmp环境
lnmp[https://lnmp.org/install.html]

#### 生成ssh key
`ssh-keygen -t rsa -C "email@test.com"`

#### 安装node

解决 npm install 权限问题
`npm install --unsafe-perm`

#### 升级gcc 
```
sudo yum install centos-release-scl
sudo yum install devtoolset-7-gcc*
scl enable devtoolset-7 bash
which gcc
gcc --version
```
