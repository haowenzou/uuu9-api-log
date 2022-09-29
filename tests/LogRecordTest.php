<?php

use Illuminate\Http\Request;

class LogRecordTest extends TestCase
{
    private $request;

    public function setUp()
    {
        $app = $this->createApplication();
        $app->withFacades();
        $app->register(\Uuu9\PhpApiLog\Provider\ApiLogServiceProvider::class);
        $this->request = $app->make(Request::class);
    }

    public function testJsonResponse()
    {
        $logRecord = new \Uuu9\PhpApiLog\Model\LogRecord($this->request, 1);
        $response = response()->json([], 200, [], JSON_UNESCAPED_UNICODE);
        $logRecord->complete($response);
        $this->assertArrayHasKey('status',$logRecord->dump());
    }
}