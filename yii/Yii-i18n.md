Yii i18n国际化

> To use the message translation service, you mainly need to do the following work:			
> 1. Wrap every text message that needs to be translated in a call to the [[Yii::t()]] method.	
> 2. Configure one or multiple message sources in which the message translation service	
can look for translated messages.	
> 3. Let the translators translate messages and store them in the message source(s)	

1.包装你的文本信息
---
```
echo \Yii::t('app', 'translate text');
```
参数1是消息分类名称，参数2是待翻译文本。

2.配置消息分类
---
Yii::t() 方法将会调用 Application component 的 translate 方法去执行实际翻译工作。该模块配置在 Application configuration 中，如下：	
```
'components' => [
	// ...
	'i18n' => [
		'translations' => [
			'app*' => [
				'class' => 'yii\i18n\PhpMessageSource',
				//'basePath' => '@app/messages',
				//'sourceLanguage' => 'en-US',
				'fileMap' => [
					'app' => 'app.php',
					'app/error' => 'error.php',
				],
		],
	],
],
```
#### 通配符 *		
app* 这个模式代表所有消息分类名以 app 开头的都将会应用上面的配置进行翻译。

3.翻译映射文件
映射文件默认放在 app/messages 目录下，假设使用上面的配置，那 Yii::t('app', 'text') 对应的文件在 app/messages/en-US/app.php ，Yii::t('app/error', 'text') 对应文件在 app/message/en-US/error.php



```
语言文件
语法转换函数
配置


代码结构
I18N
->MessageSource
	->phpmessagesource  (php file->array) 常用
	->gettextMessageSource (po,mo file)
	->dbmessagesource (db)
->MessageFormat
->Formatter

->MissingTranslationEvent
```