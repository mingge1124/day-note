## SELECT 2使用

> CDN地址

```
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>	 	
```

参数简述

```
$('select[name="test"]').select2({
	 placeholder: 'Select an option',
	 // ajax： function(){},
	 allowClear: true,    //在select框增加删除icon，点击删除所有已选选项，一般用在多选场景
	 closeOnSelect: false,   //强制select下拉在选择后不关闭，一般用在多选场景，点击结果框才会关闭下拉框。默认true.
	 data: [
		    {
		        id: 0,
		        text: 'enhancement'
		    },
		    {
		        id: 1,
		        text: 'bug'
		    },
	], //对象数组，初始化下拉option，格式必须至少有id，text两个值，id等同optione的value，text等同option的html内容
	//disabled: true, //无效化
	language: 'es', //语言
	maximumInputLength: 2,
	multiple: true, //多选
	selectOnClose: true,  //关闭下拉自动填充第一个值
	//templateResult: function(){return null}, //定义搜索结果的渲染模板
	//templateSelection: function(){return null},  //渲染下拉option的渲染模板
	width: 'element' //定义宽度
});
```

option对象格式
```
{
  "id": "value attribute" || "option text",
  "text": "label attribute" || "option text",
  "element": HTMLOptionElement，
  "disabled":true,
}
```

optgroup对象格式
```
{
  "text": "label attribute",
  "children": [ option data object, ... ],
  "element": HTMLOptGroupElement
}
```

data数组格式化

```
//创建id属性
var data = $.map(yourArrayData, function (obj) {
  obj.id = obj.id || obj.pk; // replace pk with your identifier

  return obj;
});

//创建text属性
var data = $.map(yourArrayData, function (obj) {
  obj.text = obj.text || obj.name; // replace name with the property used for the text

  return obj;
});
```

ajax动态加载下拉

```
$('#mySelect2').select2({
  ajax: {
    url: 'https://api.github.com/orgs/select2/repos',
    data: function (params) {    //请求参数
      var query = {
      	 delay: 250 // wait 250 milliseconds before triggering the request
         search: params.term,   //params 包含term,q,_type,page四个参数，term和q都代表输入框的内容，_type通常值为query,但会受query_append(用于分页请求)影响，page为当前页码
         page: params.page || 1
      }

      // Query parameters will be ?search=[term]&type=public
      return query;
    }
    processResults: function (data) {    //格式化返回参数
      // Tranforms the top-level key of the response object from 'items' to 'results'
       //服务器端返回results数组对象，需要符合data options的要求，count_filtered代表总页数，计算是否还存在下一页
       return {
	        results: data.results,
	        pagination: {
	            more: (params.page * 10) < data.count_filtered
	        }
	    };
    }
  }
});
```


设置默认值，对ajax动态加载的数据无效

```
$('#mySelect').val('1').trigger('change');  //单选
$('#mySelect').val(['1','3']).trigger('change');  //多选

```

清空选择框

```
$('#mySelect2').val(null).trigger('change');
```


常见问题

* bootstrap modal中的select框不生效。[传送门](https://select2.org/troubleshooting/common-problems) 