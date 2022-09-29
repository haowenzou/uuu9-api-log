<?php

namespace Uuu9\PhpApiLog\Model;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Uuu9\PhpApiLog\Constants;
use Uuu9\PhpApiLog\Util\HeaderUtil;

class UserInfo implements \JsonSerializable
{
    const UID_NONE = -1;

    private $uid;
    private $userAgent;
    private $request;
    private $channel;
    private $deviceType;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->userAgent = $this->getUserAgent();
        $this->uid = $this->getUid();
        $this->channel = $this->getChannel();
        $this->deviceType = $this->getDeviceType();
    }

    public function getUserAgent()
    {
        if ($this->userAgent) {
            return $this->userAgent;
        }

        $ua = HeaderUtil::getHeader($this->request, Constants::HDR_UA, null);
        if ($ua === null) {
            $ua = HeaderUtil::getHeader($this->request, 'User-Agent', "");
        }
        return $ua;
    }

    public function getUid()
    {
        if ($this->uid) {
            return $this->uid;
        }

        try {
            $uid = (int)Auth::id();
        } catch (\InvalidArgumentException $e) {
            $uid = self::UID_NONE;
        }
        return $uid ? $uid : self::UID_NONE;
    }

    public function getChannel()
    {
        if ($this->channel) {
            return $this->channel;
        }

        $channel = HeaderUtil::getHeader($this->request, Constants::HDR_CHANNEL, null);
        if ($channel === null) {
            $channel = HeaderUtil::getHeader($this->request, 'channel', "");
            if (empty($channel)) {
                $deviceType = $this->getDeviceType();
                //IOS默认渠道IOS
                if ($deviceType == "ios") {
                    $channel = "ios";
                }
            }
        }
        return strtolower($channel);
    }

    public function getDeviceType()
    {
        if ($this->deviceType) {
            return $this->deviceType;
        }

        $deviceType = HeaderUtil::getHeader($this->request, Constants::HDR_DT, null);
        if ($deviceType === null) {
            $deviceType = HeaderUtil::getHeader($this->request, 'device-type', "");
        }
        return strtolower($deviceType);
    }

    //指定对象需要被序列化成 JSON 的格式

    public function jsonSerialize()
    {
        return [
            'uid' => $this->uid,
            'ua' => $this->userAgent,
            'channel' => $this->channel,
            'dt' => $this->deviceType,
        ];
    }
}