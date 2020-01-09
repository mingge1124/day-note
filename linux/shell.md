### shell语法

#### if 结构
如果if后面的命令有多个，则只用最后一个作为判断条件
```
if commands; then
commands
[elif commands; then
commands...]
[else
commands]
fi
```

文件表达式
`[ -e file ]` 文件存在
`[ -f file ]` 是文件
`[ -d file ]` 是目录
`[ -r file ]` 文件存在且可读
`[ -w file ]` 文件存在且可写
`[ -x file ]` 文件存在且可执行

字符串表达式
`[ string ]` 字符串是否为null，空不等于null
`[ -n string ]` 字符串长度大于0
`[ -z string ]` 字符串长度等于0
`[ string1 == string2 ]`
`[ string1 != string2 ]`

整型表达式
`[ integer1 -eq integer2 ]` 相等
`[ integer1 -ne integer2 ]` 不相等
`[ integer1 -lt integer2 ]` 小于
`[ integer1 -le integer2 ]` 小等于
`[ integer1 -gt integer2 ]` 大于
`[ integer1 -ge integer2 ]` 大等于

`[[ expression ]]` 
新增支持字符串正则表达式比较 ` string =~ regex`
`if [[ "$numer" =~ ^-?[0-9]+$ ]]; then ...` 变量number是否为数字
新增==操作符支持类型匹配
`if [[ $filename == foo.* ]]; then`

`(( expression ))` 用于整数算术运算
`if (( (($int%2)) == 0 ));` 整除2

与或非
test或`[]` 下用 与`-a` 或`-o` 非`!` 
`[[]]`或`(())` 下用 与`&&` 或`||` 非`!` 

### 循环结构
break 退出，continue跳到下一循环
#### while 循环
```
while commands; do
commands
done
```

循环读取文件
```
while read xxx; do
commands
done < file.txt
```

#### untile循环
直到达到什么条件，才退出
```
until commands; do
commands
done
```

#### for循环
```
for variable [in words]; do
commands
done
```

新增c语言格式
```
for (( expression1; expression2; expression3 )); do
commands
done
```

`for i in distros*.txt; do echo $i; done`

### case结构
```
case 变量 in
	[pattern[|pattern]]) commands ;;
esac
```

`;;` 表示只要匹配到一个条件就结束，`;;&`支持执行完当前条件继续匹配下一条

```
#!/bin/bash
read -p "enter word > "
case $REPLY in
[[:alpha:]]) echo "is a single alphabetic character." ;;
[ABC][0-9]) echo "is A, B, or C followed by a digit." ;;
???) echo "is three characters long." ;;
*.txt) echo "is a word ending in '.txt'" ;;
*) echo "is something else." ;;
esac
```

### read命令接收输入


#### 退出状态
`$?` 当命令执行完毕后，命令（包括我们编写的脚本和 shell 函数）会给系统发送一个值，叫做退出
状态。这个值是一个 0 到 255 之间的整数，说明命令执行成功或是失败。按照惯例，一个零值
说明成功，其它所有值说明失败
