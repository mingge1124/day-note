问题：攻击方模拟了接口所需签名，接口本身无需登录，只需要验证签名正确即可请求

特征：
1.每次请求ip都不相同，
2.user_agent相同
3.签名内容相同
"$http_version $http_imei $http_channel $http_sign $http_time $http_nonce $http_plat" =》 
012900 355757010002240 base 0125ae83e1dfb278ed9a2f50b67db5b9 1568792656 lzvP3 1

拦截思路：
首先应该找到攻击方请求的共性特征，对该特征做拦截

签名相同可根据签名内的imei值在nginx做拦截
```
if ($http_imei ~ '355757010002240') {
	return 403 "error";
}
```

剩余问题：
拦截是为了在nginx层就截断请求，使这些请求无法进入业务逻辑层，
但是请求量大也需要注意nginx access_log的大小
