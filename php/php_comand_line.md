# php命令行

`php -a`
---     
简介：window平台下，为`interactive mode`，`ctrl+z` 作为结束，代码需要`<?php?>`包裹；linux平台为`interactive shell`，`ctrl+d`作为结束；     
示例：
```
$ php -a
Ineractive mode

<?php
echo 1+2;
?>
ctrl+z //在新的一行按下，再按下enter键

```


`php -r`
---
简介：运行php代码；
示例：
```
php -r "echo '123';"
```



