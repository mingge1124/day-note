#### 单线程模型
语言本身设计目的就是浏览器脚本语言，多线程过于复杂，需要处理共享资源，执行顺序等问题。

#### js
1 变量二次声明无效
2 JavaScript 引擎的工作方式是，先解析代码，获取所有被声明的变量，然后再一行一行地运行。这造成的结果，就是所有的变量的声明语句，都会被提升到代码的头部，这就叫做变量提升（hoisting），注意：赋值不会提升。
```
console.log(a); //不会报a未定义
var a = 1;

//等同于
var a;
console.log(a);
a=1;

```
#### 数据类型
1 number (基本)
2 bool (基本)
3 string (基本)
4 undefind
5 null
6 object

#### 变量存储
基本数据类型（number，bool，string）直接在栈内存中存储，值与值之间是独立存储，修改一个变量不会影响其他变量；

对象（引用数据类型）是保存在堆内存中的，每创建一个新对象就会在堆内存中开辟一个新的空间，而变量保存的是对象的内存地址，
如果两个变量保存的是同一个对象地址，那么当一个变量修改属性会影响另一个变量
```
var a = {"t":1};
var b = a;
b.t = 2;
console.log(a.t);  // 结果为2
```


#### 比较运算符
1 非相等运算符-字符串的比较：从首字符开始，比较unicode码点大小，按顺序一个个比较
```
'cat' > 'dog' //true
'cat' > 'Cat' //true  'c'的码点大于'C'的码点
```

2 非相等运算符-非字符串的比较（至少一个不是字符串）：
原始类型值：两个运算子都是原始类型值，先转成数值再比较
```
5 > '4' // true，等同于 5 > 4
true > false //true，等同于 1>0
2 > true //true，等同于 2 > 1

//注意 NaN与任何数据不等，包括本身
NaN == NaN //false
NaN == null //false
```

对象：运算子为对象时，会先转成原始类型在比较。对象转换成原始类型的值，算法是先调用valueOf方法；如果返回的还是对象，再接着调用toString方法
```
[11] > 2 //true，等同于 [11].valueOf().toString() > 2 即 '11' > 2 即 11 > 2 （非字符串比较，转成数值再比较）
[11] > '2' //false，等同于 [11].valueOf().toString() > '2' 即 '11' > '2' （字符串比较unicode码）
 
```

3 严格相等运算符
两个复合类型（对象、数组、函数）的数据比较时，不是比较它们的值是否相等，而是比较它们是否指向同一个地址。
```
[] === [] //false
{} === {} //fasle

//变量引用同一个对象，则相等
var a = {};
b = a;
b === a; //true
```

4 相等运算符
比较不同类型的数据时，相等运算符会先将数据进行类型转换，然后再用严格相等运算符比较。

undefined和null与其他类型的值比较时，结果都为false，它们互相比较时结果为true。

4 在if中进行强制转换为false的情况只有四种，分别是：
1.数字0
2.NaN
3.空字符串
4.null或undefined


#### 对象引用
```
var a = {name:1};
b = a; //a,b 两者共用同一份内存
b.value=2;
//===> a.value 也等于2

function test(obj) {
	obj.name2 = 2;
}
test(a);
//====> a.name2 = 2;  函数对象传参，对象也会被改变

```

### 函数
1 函数提升
```
//下面执行不会报错，因为函数声明会被自动提到代码最上面
f();
function f() {console.log(1)};

//会报错，只会提升声明，不会提示赋值
f();
var f = function() {console.log(2)};
//等同于
var f;
f();
f = function() {console.log(2)};
```

2 函数的name属性
```
function f1(){}
//===>f1.name = 'f1'

var f2 = function(){};
//===>f2.name = 'f2'

var f3 = function f4(){};
//===> f3.name = 'f4'
```

3 其他属性
length：返回函数声明参数个数，动态传参不计算
toString(): 返回函数源码，原生函数只返回原生代码提示
arguments: 函数内的arguments存放所有传参，arguments.length：返回实际传参个数

4 作用域 ：函数执行时所在的作用域，是定义时的作用域，而不是调用时所在的作用域
```
//外部作用域
var a = 1;
var x = function () {
  console.log(a);
};

function f() {
  var a = 2;
  x();
}

f() // 1


//内部作用域
function foo() {
  var x = 1;
  function bar() {
    console.log(x);
  }
  return bar;
}

var x = 2;
var f = foo();
f() // 1

//函数不能访问不同作用域的变量
var x = function () {
  console.log(a);
};

function y(f) {
  var a = 2;
  f();
}

y(x)
// ReferenceError: a is not defined
```

### 闭包
1 闭包的最大用处有两个，一个是可以读取函数内部的变量，另一个就是让这些变量始终保持在内存中，即闭包可以使得它诞生环境一直存在
```
function createIncrementor(start) {
  return function () {
    return start++;
  };
}

var inc = createIncrementor(5);

inc() // 5
inc() // 6
inc() // 7
```

2 闭包的另一个用处，是封装对象的私有属性和私有方法。
```
function Person(name) {
  var _age;
  function setAge(n) {
    _age = n;
  }
  function getAge() {
    return _age;
  }

  return {
    name: name,
    getAge: getAge,
    setAge: setAge
  };
}

var p1 = Person('张三');
p1.setAge(25);
p1.getAge() // 25
```

### IIFE:Immediately Invoked Function Expression 立即调用的函数表达式
作用：一是不必为函数命名，避免了污染全局变量；二是 IIFE 内部形成了一个单独的作用域，可以封装一些外部无法读取的私有变量
做法：用()将函数声明包起来，让js编译器不在认为是一个函数声明
```
//写法1
(function() {
	console.log(1);
}());

//写法2，更常用
(function(){
  console.log(1);
})();
```

#### Object

利用Object.prototype.toString判断数据类型
```
var type = function (o){
  var s = Object.prototype.toString.call(o);
  return s.match(/\[object (.*?)\]/)[1].toLowerCase();
};

['Null',
 'Undefined',
 'Object',
 'Array',
 'String',
 'Number',
 'Boolean',
 'Function',
 'RegExp'
].forEach(function (t) {
  type['is' + t] = function (o) {
    return type(o) === t.toLowerCase();
  };
});

type.isObject({}) // true
type.isNumber(NaN) // true
type.isRegExp(/abc/) // true
```

#### String
正则
利用g修饰符允许多次匹配的特点，可以用一个循环完成全部匹配。
```
var reg = /a/g;
var str = 'abc_abc_abc'

while(true) {
  var match = reg.exec(str);
  if (!match) break;
  console.log('#' + match.index + ':' + match[0]);
}
```

预定义模式
```
\d 匹配0-9之间的任一数字，相当于[0-9]。
\D 匹配所有0-9以外的字符，相当于[^0-9]。
\w 匹配任意的字母、数字和下划线，相当于[A-Za-z0-9_]。
\W 除所有字母、数字和下划线以外的字符，相当于[^A-Za-z0-9_]。
\s 匹配空格（包括换行符、制表符、空格符等），相等于[ \t\r\n\v\f]。
\S 匹配非空格的字符，相当于[^ \t\r\n\v\f]。
\b 匹配词的边界。
\B 匹配非词边界，即在词的内部。
```

正则替换所有匹配值
```
'aaa'.replace(/a/g, 'b') // "bbb"
```

#### 面向对象
new 执行原理：
1 创建一个空对象，作为将要返回的对象实例。
2 将这个空对象的原型，指向构造函数的prototype属性。
3 将这个空对象赋值给函数内部的this关键字。
4 开始执行构造函数内部的代码。

this作用域问题：
```
var o = {
  v: 'hello',
  p: [ 'a1', 'a2' ],
  f: function f() {
    this.p.forEach(function (item) {
      console.log(this.v + ' ' + item);   //this 此处等同于window对象
    });
  }
}

o.f();

//解决：使用中间变量固定this(重要，常用)
var o = {
  v: 'hello',
  p: [ 'a1', 'a2' ],
  f: function f() {
    var that = this;							
    this.p.forEach(function (item) {
      console.log(that.v + ' ' + item);
    });
  }
}

o.f()
```



















































