Yii2 Id Generator
---------------------------------------

本扩展是Twitter的Snowflake算法的实现, 然而由于PHP的限制, 该算法的PHP实现无法保证结果绝对的唯一性. 因此在PHP环境下必须借助其它语言(例如C), 但这需要你对服务器有绝对的控制权(例如Iaas云服务, Paas则不行), 这也增加了部署的工作量.

无法实现的原因:
*	PHP总是返回相同的pid
*	PHP无法维护 Sequence, 当请求结束时 Sequence 又被初始化
*	PHP没有线程概念无法对资源实施线程锁

本扩展是Snowflake的PHP实现, 它同样存在上诉问题, 下面是一组测试数据:

唯一性测试(workid随机生成):
*	34个并发请求, 每个请求生成1000个ID, **结果: 重复35个ID, 概率: 35/34000 = 0.1029%(千分之1.029)**
*	单并发生成20000个ID, **结果: 重复7个ID, 概率: 7/20000 = 0.035%(万分之3.5)**

重复概率还是比较低的,   **由于Snowflake的优点** 和 **应用可扩展性的重要性** 因此决定实现一个可用的Snowflake: `yii2-idGenerator`

**实现原则: 唯一性、业务适合性是可以权衡的, 当出现重复ID时可以安排错误处理和恢复机制来解决问题**

因此仅仅实现Snowflake算法是不够的, 本扩展还提供了:
*	提供一个包装方法, 使 调用者 无需关心ID重复问题: 
	实现捕获`yii\db\IntegrityException`异常恢复机制提供重新生成ID的机会, 并可设定重复生成ID的次数. 
*	扩展 `yii\db\ActiveRecord`, 使 调用者 在使用AR对象时无需关心ID重复问题
*	虽然Snowflake生成64bit的ID, 但本算法可以在32bit的PHP中运行.

Snowflake可以摆脱MySql的自增ID, 来提高应用的可扩展性, 以下是一些观点:
```
不要迷信数据库性能，不要迷信三范式，不要使用外键，不要使用byte，不要使用自增id，不要使用存储过程，不要使用内部函数，不要使用非标准sql，存储系统只做存储系统的事。当出现系统性能时，如此设计的数据库可以更好的实现迁移数据库（如MySQL->oracle)，实现nosql改造（(MongoDB/Hadoop），实现key-value缓存(Redis,memcache)。

很多程序员有对性能认识有误区，如使用存储过程代替正常程序，其实使用存储过程只是追求单服务器的高性能，当需要服务器水平扩展时，存储过程中的业务逻辑就是你的噩运。

```


Installation
------------

### Install With Composer

安装这个扩展的首选方式是通过 [composer](http://getcomposer.org/download/). 

```
php composer.phar require superlyons/yii2-id-generator "dev-master"
```
或者, 你也可以添加下面的代码到你的`composer.json`文件中

```
"superlyons/yii2-id-generator":"dev-master"
```