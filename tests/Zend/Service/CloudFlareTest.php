<?php

/** Zend_Service_CloudFlare */
require_once 'Zend/Service/CloudFlare.php';

class Zend_Service_CloudFlareTest extends PHPUnit_Framework_TestCase
{
    /**
     * Runs the test methods of this class.
     *
     * @return void
     */
    public static function main()
    {
        $suite  = new PHPUnit_Framework_TestSuite(__CLASS__);
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    protected function _stub($method, $expectedResponse, array $options = array(), $stub = null)
    {
        if (is_null($stub)) {
            // Create a stub for the Zend_Service_CloudFlare class.
            $stub = $this->getMockBuilder('Zend_Service_CloudFlare')
                ->setConstructorArgs(array('some_bogus_api_key'))
                ->getMock();
        }

        $returnValue = call_user_func(array($this, $expectedResponse));

        // Configure the stub.
        $stub->expects($this->any())
             ->method($method)
             ->will($this->returnValue($returnValue));

        return call_user_func(array($stub, $method));
    }

    public function testShouldInstantiate()
    {
        $cloudFlare = new Zend_Service_CloudFlare();
        $this->assertInstanceOf('Zend_Service_CloudFlare', $cloudFlare);
    }

    public function testShouldBeInvalidResponseIfNoApiKeyPassed()
    {
        $stub     = $this->getMock('Zend_Service_CloudFlare');
        $response = $this->_userCreate("_invalidResponseIfNoApiKeyPassed", array(), $stub);
        $this->assertEquals("user_create"            , $response['request']['act']);
        $this->assertEquals("error"                  , $response['result']);
        $this->assertEquals("No of invalid host_key.", $response['msg']);
    }

    public function testShouldCreateUser()
    {
        $response = $this->_userCreate("_successfulUserCreateResponse");
        $this->assertEquals("user_create", $response['request']['act']);
        $this->assertEquals("success"    , $response['result']);
        $this->assertNull($response['msg']);
    }

    public function testShouldSetZone()
    {
        $response = $this->_zoneSet("_successfulZoneSetResponse");
        $this->assertEquals("zone_set"                         , $response['request']['act']);
        $this->assertEquals("success"                          , $response['result']);
        $this->assertEquals("www.sample.com.cdn.cloudflare.net", $response['response']['forward_tos']["www.sample.com"]);
    }

    public function testShouldLookupUser()
    {
        $response = $this->_userLookup("_successfulUserLookupResponse");
        $this->assertEquals("user_lookup", $response['request']['act']);
        $this->assertEquals("success"    , $response['result']);
        $this->assertEquals("sample.com" , $response['response']['hosted_zones'][0]);
    }

    public function testShouldAuthUser()
    {
        $response = $this->_userAuth("_successfulUserAuthResponse");
        $this->assertEquals("user_auth"    , $response['request']['act']);
        $this->assertEquals("success"      , $response['result']);
        $this->assertEquals("some_user_key", $response['response']['user_key']);
    }

    public function testShouldNotLookupZoneIfNoUserKey()
    {
        $response = $this->_userLookup("_unsuccessfulZoneLookupResponseIfNoUserKey");
        $this->assertEquals("zone_lookup" , $response['request']['act']);
        $this->assertEquals("error"       , $response['result']);
        $this->assertEquals("No user_key.", $response['msg']);
        $this->assertEquals(115           , $response['err_code']);
    }

    public function testShouldLookupZone()
    {
        $options = array('user_key'  => 'some_user_key');
        $response = $this->_userLookup("_successfulZoneLookupResponseWithResult", $options);
        $this->assertEquals("zone_lookup", $response['request']['act']);
        $this->assertEquals("success"    , $response['result'] );
        $this->assertTrue($response['response']['zone_exists']);
    }

    public function testShouldLookupZoneWithNoResult()
    {
        $options = array('user_key'  => 'some_user_key');
        $response = $this->_userLookup("_successfulZoneLookupResponseWithNoResult", $options);
        $this->assertEquals("zone_lookup", $response['request']['act']);
        $this->assertEquals("success"    , $response['result'] );
        $this->assertFalse($response['response']['zone_exists']);
        $this->assertFalse($response['response']['zone_hosted']);
    }

    public function testShouldDeleteZone()
    {
        $response = $this->_zoneDelete("_successfulZoneDeleteResponse");
        $this->assertEquals("zone_delete", $response['request']['act']);
        $this->assertEquals("success"    , $response['result']);
        $this->assertTrue($response['response']['zone_deleted']);
    }

    private function _userCreate($expectedResponse, array $options = array(), $stub = null)
    {
        $options = array_merge(
            array(
                "cloudflare_email"    => "bogus@sample.com",
                "cloudflare_pass"     => "some_password",
                "cloudflare_username" => "someuser",
            ), $options
        );
        return $this->_stub("userCreate", $expectedResponse, $options, $stub);
    }

    private function _zoneSet($expectedResponse, array $options = array(), $stub = null)
    {
        $options = array_merge(
            array(
                "user_key"   => "some_user_key",
                "zone_name"  => "example.com",
                "resolve_to" => "resolve-to-cloudflare.example.com",
                "subdomains" => "www.example.com,blog.example.com",
            ), $options
        );
        return $this->_stub("zoneSet", $expectedResponse, $options, $stub);
    }

    private function _userLookup($expectedResponse, array $options = array(), $stub = null)
    {
        $options = array_merge(
            array(
                'cloudflare_email' => 'bogus@sample.com',
            ), $options
        );
        return $this->_stub("userLookup", $expectedResponse, $options, $stub);
    }

    private function _userAuth($expectedResponse, array $options = array(), $stub = null)
    {
        $options = array_merge(
            array(
                'cloudflare_email' => 'bogus@sample.com',
                'cloudflare_pass'  => 'some_password',
            ), $options
        );
        return $this->_stub("userAuth", $expectedResponse, $options, $stub);
    }

    private function _zoneLookup($expectedResponse, array $options = array(), $stub = null)
    {
        $options = array_merge(
            array(
                'zone_name' => 'sample.com',
            ), $options
        );
        return $this->_stub("zoneLookup", $expectedResponse, $options, $stub);
    }

    private function _zoneDelete($expectedResponse, array $options = array(), $stub = null)
    {
        $options = array_merge(
            array(
                'zone_name' => 'sample.com',
                'user_key'  => 'some_user_key',
            ), $options
        );
        return $this->_stub("zoneDelete", $expectedResponse, $options, $stub);
    }

    /*
     * Responses
     */
    private function _invalidResponseIfNoApiKeyPassed()
    {
        return array (
            'request' => array (
                'act' => 'user_create',
            ),
            'result'   => 'error',
            'msg'      => 'No of invalid host_key.',
            'err_code' => 100,
        );
    }

    private function _successfulUserCreateResponse()
    {
        return array (
            'request' => array (
                'act' => 'user_create',
            ),
            'response' => array (
                'cloudflare_email'    => 'bogus@sample.com',
                'user_key'            => 'some_user_key',
                'unique_id'           => NULL,
                'cloudflare_username' => 'someuser',
                'user_api_key'        => 'some_api_key',
            ),
            'result' => 'success',
            'msg'    => NULL,
        );
    }

    private function _successfulZoneSetResponse()
    {
        return array (
            'request' => array (
                'act'       => 'zone_set',
                'zone_name' => 'sample.com',
            ),
            'response' => array (
                'zone_name'    => 'sample.com',
                'resolving_to' => '123.45.678.901',
                'hosted_cnames' => array (
                    'www.sample.com' => '123.45.678.901',
                ),
                'forward_tos' => array (
                    'www.sample.com' => 'www.sample.com.cdn.cloudflare.net',
                ),
            ),
            'result' => 'success',
            'msg'    => NULL,
        );
    }

    private function _successfulUserLookupResponse()
    {
        return array (
            'request' => array (
                'act'              => 'user_lookup',
                'cloudflare_email' => 'bogus@sample.com',
            ),
            'response' => array (
                'user_exists'      => true,
                'cloudflare_email' => 'bogus@sample.com',
                'user_authed'      => true,
                'user_key'         => 'some_user_key',
                'unique_id'        => NULL,
                'hosted_zones'     => array (
                    0 => 'sample.com',
                ),
                'user_api_key' => 'some_api_key',
            ),
            'result' => 'success',
            'msg'    => NULL,
        );
    }

    private function _successfulUserAuthResponse()
    {
        return array (
            'request' => array (
                'act' => 'user_auth',
            ),
            'response' => array (
                'cloudflare_email' => 'bogus@sample.com',
                'user_key'         => 'some_user_key',
                'unique_id'        => NULL,
                'user_api_key'     => 'some_api_key',
            ),
            'result' => 'success',
            'msg'    => NULL,
        );
    }

    private function _unsuccessfulZoneLookupResponseIfNoUserKey()
    {
        return array (
          'request' => array (
            'act' => 'zone_lookup',
          ),
          'result' => 'error',
          'msg' => 'No user_key.',
          'err_code' => 115,
        );
    }

    private function _successfulZoneLookupResponseWithResult()
    {
        return array (
          'request' => array (
            'act'       => 'zone_lookup',
            'zone_name' => 'sample.com',
          ),
          'response' => array (
            'zone_name'     => 'sample.com',
            'zone_exists'   => true,
            'zone_hosted'   => true,
            'hosted_cnames' => array (
              'sample.com' => '123.45.678.901',
              'www.sample.com' => '123.45.678.901',
            ),
            'forward_tos' => array (
              'sample.com'     => 'sample.com.cdn.cloudflare.net',
              'www.sample.com' => 'www.sample.com.cdn.cloudflare.net',
            ),
          ),
          'result' => 'success',
          'msg' => NULL,
        );
    }

    private function _successfulZoneLookupResponseWithNoResult()
    {
        return array (
            'request' => array (
                'act'       => 'zone_lookup',
                'zone_name' => 'sample.com',
            ),
            'response' => array (
                'zone_name'     => 'sample.com',
                'zone_exists'   => false,
                'zone_hosted'   => false,
                'hosted_cnames' => NULL,
                'forward_tos'   => NULL,
            ),
            'result' => 'success',
            'msg' => NULL,
        );
    }

    private function _successfulZoneDeleteResponse()
    {
        return array (
          'request' => array (
            'act'       => 'zone_delete',
            'zone_name' => 'sample.com',
          ),
          'response' => array (
            'zone_name'    => 'sample.com',
            'zone_deleted' => true,
          ),
          'result' => 'success',
          'msg'    => NULL,
        );
    }
}
