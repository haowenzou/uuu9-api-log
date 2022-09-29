<?php

namespace Uuu9\PhpApiLog\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Uuu9\PhpApiLog\Emitter\LogEmitter;
use Uuu9\PhpApiLog\Model\LogRecord;
use Uuu9\PhpApiLog\Util\HeaderUtil;

class ApiLog
{
    private $record;

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $rid = (String)HeaderUtil::getRid($request);
            $this->record = new LogRecord($request, $rid);
            Cache::store('array')->put('penetrateContext', $this->record->getPenetrateContext(), 1);
        } catch (\Throwable $e) {
            Log::error("Api Log实例化异常：" . $request->getPathInfo() . $e->getMessage() . $e->getTraceAsString());
        }
        
        return $next($request);
    }

    /**
     * 异步调用
     * 原理:1、symfony/http-foundation/Response send函数调用了fastcgi_finish_request
     * 2、terminate()的执行顺序在 Response->send() 后
     * @param $request
     * @param $response
     */
    public function terminate($request, $response)
    {
        try {
            $this->record->complete($response);
            $logEmitter = new LogEmitter();
            $logEmitter->emit($this->record);
        } catch (\AliyunSLS\Exception $e) {
            Log::error("Api Log Aliyun上报异常：" . $e->getMessage() . $e->getTraceAsString() . json_encode($this->record->dump()));
        } catch (\Throwable $e) {
            Log::error("Api Log执行异常：" . $request->getPathInfo() . $e->getMessage() . $e->getTraceAsString());
        }
    }
}