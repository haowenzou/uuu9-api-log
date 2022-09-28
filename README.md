# uuu9-api-log
## 依赖

## 代码参考 TODO
http://git.vpgame.cn/infra/vp-java-api-log

## 支持框架
- lumen5.3
- lumen5.4

## lumen项目配置
1 `composer.json` repositories节点新增
```json
{
    "type": "vcs",
    "url": "git@github.com:haowenzou/uuu9-php-api-log.git"
},
{
    "type": "vcs",
    "url": "git@github.com:haowenzou/uuu9-aliyun-sdk-sls.git"
}
```

2 运行命令
```bash
composer require uuu9/php-api-log
```

3 `bootstrap/app.php`
```php
//注册ApiLog
$app->register(Uuu9\PhpApiLog\Provider\ApiLogServiceProvider::class);
```

4 `app/Providers/AppServiceProvider.php`
```php
Uuid::generate()
//修改为
Uuid::generate(4)
```

5 `.env.tp`

```
## 阿里云SLS配置 [/config/aliyun/sls/api_log]
SLS_API_LOG_ENDPOINT=null
SLS_API_LOG_ACCESS_KEY=null
SLS_API_SECRET_KEY=null
SLS_API_LOG_CURL_TIME_OUT_MS=null //默认1s(异步调用)
SLS_API_LOG_LOG_STORE=null //默认apilog
SLS_API_LOG_TOPIC={项目名称}-语言{php,java,fed,go}
SLS_API_LOG_PROJECT=null


//配置示例：IPDB项目
## 阿里云SLS配置 [/config/aliyun/sls/api_log]
SLS_API_LOG_ENDPOINT=null
SLS_API_LOG_ACCESS_KEY=null
SLS_API_SECRET_KEY=null
SLS_API_LOG_CURL_TIME_OUT_MS=null 
SLS_API_LOG_LOG_STORE=null
SLS_API_LOG_TOPIC=ipdb-php
SLS_API_LOG_PROJECT=null
```



6 升级`uuu9/signature`
```bash
//版本 >= 0.3.1
composer update uuu9/signature
```

## 透传头域

对于需要透传的头域，SDK通过 Cache 的方式传递给应用程序：

```php
//通过此方式获取
Cache::store('array')->get('penetrateContext')
```
包含了：

- X-Request-Id
- X-Remote-Addr
- X-User-Info-UID
- X-User-Info-UA
- X-User-Info-Channel
- X-User-Info-DT
- X-Forwarded-For
- X-STA-Id
- X-Transaction-Id

如果Cache中不存在，则表明该头域不需要透传。


## 参考

1. [通用API日志规范](http://git.vpgame.cn/infra/design-docs/blob/master/draft/api-log/api-log.md)

