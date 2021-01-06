# swoft_log_handler

> Swoft2的日志没有文件数量限制，会导致服务器的日志越积越多

该项目将Monolog中`RotatingFileHandler`的实现逻辑搬过来，同时继承Swoft2的`FileHandler`类。

每次生成日志文件时，都会检查相应的日志文件数量，并删除最老的日志。

## 使用

```sh
composer require anhoder/swoft-log-handler
```

在`app/bean.php`下使用：

```php
'testHandler'   => [
    'class'     => Anhoder\Swoft\Log\RotatingFileHandler::class,
    'logFile'   => '@runtime/logs/test/test-%d{Y-m-d}.log',
    'levels'    => 'notice,info,debug,trace,error,warning',
    'formatter' => bean('lineFormatter'),
    'maxFiles'  => 3,
],
```
