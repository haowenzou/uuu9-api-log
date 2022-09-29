<?php

use Illuminate\Http\Request;

class MiddleWareTest extends TestCase
{
    const TEST_USER_ID = 123;
    private $request;

    public function setUp()
    {
        parent::setUp();
        $this->app->withFacades();

        $this->request = $this->app->make(Request::class);
        $this->app->register(\Uuu9\PhpApiLog\Provider\ApiLogServiceProvider::class);

        //测试用户
        $this->app['auth']->viaRequest('api', function ($request) {
            return new \Illuminate\Auth\GenericUser(['id' => self::TEST_USER_ID]);
        });

        //测试
        $this->app->get("/testRoute", function () {
            return "testRouterResponse";
        });
        $this->app->get("/testRouteJson", function () {
            return ['data' => ['key' => 'value']];
        });

        //清空缓存
        \Illuminate\Support\Facades\Cache::store('array')->flush();
    }

    public function testMiddleWareWithStringResponse()
    {
        $this->get("/testRoute");
        $this->assertEquals(200, $this->response->getStatusCode());
        $this->assertEquals('testRouterResponse', $this->response->getContent());
    }


    public function testMiddleWareWithJsonResponse()
    {
        $this->get("/testRouteJson");
        $this->assertEquals(200, $this->response->getStatusCode());
        $this->assertEquals('{"data":{"key":"value"}}', $this->response->getContent());
        $this->assertEquals(['data' => ['key' => 'value']], $this->response->getOriginalContent());
    }

    public function testPenetrateContext(){
        $this->get("/testRoute");
        $this->assertArrayHasKey(\Uuu9\PhpApiLog\Constants::HDR_RID,\Illuminate\Support\Facades\Cache::store('array')->get('penetrateContext'));
        $this->assertArrayHasKey(\Uuu9\PhpApiLog\Constants::HDR_UID,\Illuminate\Support\Facades\Cache::store('array')->get('penetrateContext'));
    }
}