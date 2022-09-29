<?php

namespace Uuu9\PhpApiLog\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Uuu9\PhpApiLog\Constants;
use Uuu9\PhpApiLog\Util\ExclusionJsonUtil;
use Uuu9\PhpApiLog\Util\HeaderUtil;

class LogRecord
{
    private $service;
    private $rid;
    private $uri;
    private $method;
    private $remoteAddr;
    private $userInfo;
    private $xff;
    private $tid;
    private $ptid;
    private $sta;
    private $clientAddr;
    private $serverAddr;
    private $stime;
    private $etime;
    private $dur;
    private $status;
    private $req;
    private $resp;
    private $hdrs;
    private $extra;

    private $request;
    private $logConfig;

    /**
     * LogRecord constructor.
     * @param Request $request
     * @param int $rid
     */
    public function __construct(Request $request, string $rid)
    {
        $this->logConfig = config(Constants::CONFIG_PREFIX);
        $this->rid = $rid;
        $this->request = $request;
        $this->service = $this->logConfig['server_name'];
        $this->stime = HeaderUtil::microtime();
        $this->uri = $this->getUri();
        $this->method = $this->getMethod();
        $this->remoteAddr = $this->getRemoteAddr();
        $this->userInfo = $this->getUserInfo();
        $this->xff = $this->getXff();
        $this->sta = $this->getSta();
        $this->tid = HeaderUtil::genTid(); //生成当前服务的 Transaction ID
        $this->ptid = $this->getTid(); //上一级的 Transaction ID
        $this->clientAddr = $this->getClientAddr();
        $this->serverAddr = $this->getServerAddr();
        $this->req = $this->getReq();
        $this->hdrs = $this->getHdrs();
    }

    /**
     * 请求的服务路径，不包括域名，请求参数等信息
     * eg: /api/users/123/
     * @return string
     */
    private function getUri()
    {
        return $this->request->getRequestUri();
    }

    /**
     * 请求方法
     * eg: GET POST
     * @return string
     */
    private function getMethod()
    {
        return $this->request->getMethod();
    }

    /**
     * 请求的原始发送方ip地址
     * 通过头域中的 X-Remote-Addr 获取和透传
     * @return string
     */
    private function getRemoteAddr()
    {
        return HeaderUtil::getHeader($this->request, Constants::HDR_REMOTE_ADDR);
    }

    /**
     * 请求的原始发送方的身份信息
     * eg: {"uid": 123, "ua": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36"}
     * @return UserInfo //OBJECT, 包括: uid - 业务相关的用户ID [INTEGER] , ua - User-Agent [STRING]
     */
    private function getUserInfo()
    {
        return new UserInfo($this->request);
    }

    /**
     * 请求的链路信息
     * eg: 129.78.138.66,129.78.64.103
     * @return string
     */
    private function getXff()
    {
        return HeaderUtil::getHeader($this->request, Constants::HDR_XFF);
    }

    /**
     * 请求的单触点归因的标识
     * eg: PUSH_1231123
     * @return string
     */
    private function getSta()
    {
        return HeaderUtil::getHeader($this->request, Constants::HDR_STA);
    }

    /**
     * 请求的事务标识
     * eg: 202cb962ac59075b964b07152d234b70
     * @return string
     */
    private function getTid()
    {
        return HeaderUtil::getHeader($this->request, Constants::HDR_TID);
    }

    /**
     * 实际服务请求方地址
     * @return string
     */
    private function getClientAddr()
    {
        return $this->request->server('REMOTE_ADDR', '');
    }

    /**
     * 服务所在的地址信息
     * @return string
     */
    private function getServerAddr()
    {
        return $this->request->server('SERVER_ADDR', '');
    }

    /**
     * 请求参数
     * 不包含文件，二进制数据等信息
     * @return array|\stdClass
     */
    private function getReq()
    {
        $reqParams = $this->getReqParams();
        $reqParams = ExclusionJsonUtil::excludeFields($reqParams, $this->logConfig['exclude_fields']);
        return empty($reqParams) ? new \stdClass() : $reqParams;
    }

    /**
     * 请求参数
     * @return array
     */
    private function getReqParams()
    {
        return $this->request->input();
    }

    /**
     * 请求头，只针对 include_hdrs 中定义的有效
     * eg: {"Content-Type": "application/json"}
     * @return array|\stdClass
     */
    private function getHdrs()
    {
        $hdrs = [];
        foreach ($this->logConfig['include_hdrs'] as $includeHdr) {
            $value = HeaderUtil::getHeader($this->request, $includeHdr);
            if ($value) {
                $hdrs[$includeHdr] = $value;
            }
        }
        return empty($hdrs) ? new \stdClass() : $hdrs;
    }

    /**
     * @param Response $response
     */
    public function complete(Response $response)
    {
        $this->etime = HeaderUtil::microtime();
        $this->dur = $this->etime - $this->stime;
        $this->status = $this->getStatus($response);
        $this->resp = $this->getResp($response);
        $this->extra = $this->getExtra($response);
    }

    /**
     * 请求处理的状态码
     * @param Response $response
     * @return int
     */
    private function getStatus(Response $response)
    {
        return $response->getStatusCode();
    }

    /**
     * 应答结果
     * eg: {"id": 100, "username": "alice", "email": "alice@gmail.com"}
     * TODO::不包含文件，二进制数据等信息? PHP业务暂无返回二进制、文件
     * @param Response $response
     * @return array|\stdClass
     */
    private function getResp(Response $response)
    {
        $respBodyArray = null;
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $respBodyArray = $response->getData(true);
        }

        if ($response instanceof \Illuminate\Http\Response) {
            $respBodyArray = $response->getOriginalContent();
        }

        //非数组或者空数组返回空对象
        if (!is_array($respBodyArray)) {
            return new \stdClass();
        }
        $respBodyArray = ExclusionJsonUtil::excludeFields($respBodyArray, $this->logConfig['exclude_fields']);
        return empty($respBodyArray) ? new \stdClass() : $respBodyArray;
    }

    /**
     * 自定义字段，默认为空对象。具体的日志客户端可根据需要进行定制。
     * @param Response|null $response
     * @return \stdClass
     */
    private function getExtra(Response $response = null)
    {
        //TODO::额外信息
        return new \stdClass();
    }

    /**
     * @return array
     */
    public function dump()
    {
        //2019-04-10T08:31:42.640Z
        $ISO8601V = "Y-m-d\TH:i:s.v\Z";
        $timezone = new \DateTimeZone('UTC');

        return [
            'service' => $this->service,
            'rid' => $this->rid,
            'uri' => $this->uri,
            'method' => $this->method,
            'remote_addr' => $this->remoteAddr,
            'xff' => $this->xff,
            'tid' => $this->tid,
            'ptid' => $this->ptid,
            'sta' => $this->sta,
            'user_info' => json_encode($this->userInfo),
            'client_addr' => $this->clientAddr,
            'server_addr' => $this->serverAddr,
            'stime' => \DateTime::createFromFormat('U.u', \bcdiv($this->stime, 1000, 3))->setTimezone($timezone)->format($ISO8601V),
            'etime' => \DateTime::createFromFormat('U.u', \bcdiv($this->etime, 1000, 3))->setTimezone($timezone)->format($ISO8601V),
            'dur' => $this->dur,
            'status' => $this->status,
            'req' => json_encode($this->req),
            'resp' => json_encode($this->resp),
            'hdrs' => json_encode($this->hdrs),
            'extra' => json_encode($this->extra),
        ];
    }

    public function getPenetrateContext()
    {
        $ctx = [];
        $ctx[Constants::HDR_RID] = $this->rid;

        if ($this->remoteAddr) {
            $ctx[Constants::HDR_REMOTE_ADDR] = $this->remoteAddr;
        }

        if ($this->userInfo->getUid() != UserInfo::UID_NONE) {
            $ctx[Constants::HDR_UID] = $this->userInfo->getUid();
        }

        if ($this->userInfo->getUserAgent()) {
            $ctx[Constants::HDR_UA] = $this->userInfo->getUserAgent();
        }

        if ($this->userInfo->getChannel()) {
            $ctx[Constants::HDR_CHANNEL] = $this->userInfo->getChannel();
        }

        if ($this->userInfo->getDeviceType()) {
            $ctx[Constants::HDR_DT] = $this->userInfo->getDeviceType();
        }

        if ($this->xff) {
            $newXff = sprintf("%s, %s", $this->xff, $this->serverAddr);
        } else {
            $newXff = $this->serverAddr;
        }
        if ($this->sta) {
            $ctx[Constants::HDR_STA] = $this->sta;
        }

        $ctx[Constants::HDR_XFF] = $newXff;
        $ctx[Constants::HDR_TID] = $this->tid;
        return $ctx;
    }

}
