<?php

namespace Uuu9\PhpApiLog;

class Constants
{
    const CONFIG_PREFIX = "vp.infra.apilog";

    const HDR_RID = "X-Request-Id";
    const HDR_REMOTE_ADDR = "X-Remote-Addr";
    const HDR_UID = "X-User-Info-UID";
    const HDR_UA = "X-User-Info-UA";
    const HDR_CHANNEL = "X-User-Info-Channel";
    const HDR_DT = "X-User-Info-DT";
    const HDR_XFF = "X-Forwarded-For";
    const HDR_STA = "X-STA-Id";
    const HDR_TID = "X-Transaction-Id";

    const MIME_JSON = "application/json";

    const ATTR_RID = "rid";
    const ATTR_RECORD = "record";
}