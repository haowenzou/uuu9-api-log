<?php

use Uuu9\PhpApiLog\Util\ExclusionJsonUtil;

class ExclusionJsonUtilTest extends TestCase
{
    private $reqParams;

    public function setUp()
    {
        /**
         *  {"f1": "v1",
         *  "f2": "v2",
         *  "f3": { "c1": "cv1",
         *          "c2": "cv2",
         *          "c3": { "cc1": "ccv1",
         *                  "cc2": "ccv2"}}}
         */
        $this->reqParams = [
            'f1' => 'v1',
            'f2' => 'v2',
            'f3' => [
                'c1' => 'cv1',
                'c2' => 'cv2',
                'c3' => [
                    'cc1' => 'ccv1',
                    'cc2' => 'ccv2'
                ]
            ]
        ];

    }

    public function testExcludeField()
    {
        $this->assertEquals('v1', $this->reqParams['f1']);
        $this->assertEquals('v2', $this->reqParams['f2']);

        $filtered = ExclusionJsonUtil::excludeFields($this->reqParams, []);
        $this->assertEquals('v1', $filtered['f1']);

        $filtered = ExclusionJsonUtil::excludeFields($this->reqParams, ["f1"]);
        $this->assertEquals(ExclusionJsonUtil::ASTERISK, $filtered['f1']);
    }

    public function testExcludeFieldNested()
    {
        $this->assertEquals('ccv1', $this->reqParams['f3']['c3']['cc1']);

        $filtered = ExclusionJsonUtil::excludeFields($this->reqParams, ['cc1']);
        $this->assertEquals(ExclusionJsonUtil::ASTERISK, $filtered['f3']['c3']['cc1']);

    }

    public function testMaxNestingLevel()
    {
        $testcases = [
            "apilog" => "apilog",
            " apilog " => "apilog",
            "api-log" => "apilog",
            "api_log" => "apilog",
            " api_log " => "apilog",
            "ApiLog" => "apilog",
            "Api-Log" => "apilog",
            " Api-Log " => "apilog",
        ];

        foreach ($testcases as $tcKey => $tcValue) {
            $this->assertEquals($tcValue, ExclusionJsonUtil::normalizeExcludeField($tcKey));
        }
    }
}