<?php

use Illuminate\Http\Request;
use Uuu9\PhpApiLog\Util\HeaderUtil;
use Webpatser\Uuid\Uuid;

class HeaderUtilTest extends TestCase
{
    private $request;

    public function setUp()
    {
        $app = $this->createApplication();
        $this->request = $app->make(Request::class);
    }

    public function testGetRidWithNoXRequestId()
    {

        $rid = HeaderUtil::getRid($this->request);
        $segs = explode(',', $rid);


        $this->assertEquals(2, count($segs));
        $this->assertTrue($segs[0] <= HeaderUtil::microtime());
        $this->assertTrue(Uuid::validate($segs[1]));
    }

    public function testGetRidWithXRequestId()
    {
        $rid = "1551146504058,290e5cf8-0a44-4763-888a-e9bfdb0c800f";
        $this->request->headers->set("X-Request-Id", $rid);

        $this->assertEquals($rid, HeaderUtil::getRid($this->request));
    }

    public function testGetHeader()
    {
        $existHeader = "Header1";
        $nonExistHeader = "Header2";

        $this->request->headers->set($existHeader, "value");
        $this->assertEquals("value", HeaderUtil::getHeader($this->request, $existHeader));
        $this->assertEquals("", HeaderUtil::getHeader($this->request, $nonExistHeader));
        $this->assertEquals("abc", HeaderUtil::getHeader($this->request, $nonExistHeader, "abc"));
    }
}