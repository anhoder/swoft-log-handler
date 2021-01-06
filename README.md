# swoft_log_handler

> Swoft2的日志没有文件数量限制，会导致服务器的日志越积越多

该项目将Monolog中`RotatingFileHandler`的实现逻辑搬过来，同时继承Swoft2的`FileHandler`类。

每次生成日志文件时，都会检查相应的日志文件数量，并删除最老的日志。
