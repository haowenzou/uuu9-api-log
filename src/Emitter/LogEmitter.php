<?php

namespace Uuu9\PhpApiLog\Emitter;

use AliyunSLS\Client;
use AliyunSLS\Models\LogItem;
use AliyunSLS\Models\Request\PutLogsRequest;
use Uuu9\PhpApiLog\Constants;
use Uuu9\PhpApiLog\Model\LogRecord;

class LogEmitter
{
    private $client;
    private $sslConfig;

    public function __construct()
    {
        $this->sslConfig = config(Constants::CONFIG_PREFIX . '.sls');
        $this->client = new Client($this->sslConfig['endpoint'], $this->sslConfig['accessKey'], $this->sslConfig['secretKey']);
    }

    public function emit(LogRecord $record)
    {
        $logItem = new LogItem();
        $logItem->setTime(time());
        $logItem->setContents($record->dump());

        $req = new PutLogsRequest($this->sslConfig['project'], $this->sslConfig['logStore'], $this->sslConfig['topic'], null, [$logItem]);

        //curl超时设置
        $curlOptions = [];
        $curlTimeOutMs = intval($this->sslConfig['curlTimeOutMs']);
        if ($curlTimeOutMs) {
            $curlOptions[CURLOPT_TIMEOUT_MS] = $curlTimeOutMs;
        }
        $this->client->putLogs($req, $curlOptions);
    }
}