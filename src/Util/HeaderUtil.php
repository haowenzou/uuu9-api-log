<?php

namespace Uuu9\PhpApiLog\Util;

use Illuminate\Http\Request;
use Uuu9\PhpApiLog\Constants;
use Webpatser\Uuid\Uuid;

class HeaderUtil
{
    public static function getRid(Request $request)
    {
        $rid = self::getHeader($request, Constants::HDR_RID, null);
        if ($rid === null) {
            $rid = self::genRid();
        }
        return $rid;
    }

    public static function getHeader(Request $request, $name, $dlft = "")
    {
        return $request->header($name, $dlft);
    }

    private static function genRid()
    {
        if (defined('X_REQUEST_ID')) {
            $uuid = X_REQUEST_ID;
        } else {
            $uuid = (string)Uuid::generate(4);
        }
        return sprintf("%d,%s", self::microtime(), $uuid);
    }

    public static function microtime()
    {
        return intval(microtime(true) * 1000);
    }

    public static function genTid()
    {
        return (string)md5(Uuid::generate(4));
    }
}