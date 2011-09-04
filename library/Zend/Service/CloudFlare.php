<?php

/*
 * Zend_Service_CloudFlare
 *
 * An easy interface to the CloudFlare Hosting Provider API.
 *
 * @see https://www.cloudflare.com/docs/host-api.html
 */

/**
 * @see Zend_Service_CloudFlare_Exception
 */
require_once 'Zend/Service/CloudFlare/Exception.php';

/**
 * @see Zend_Service_Abstract
 */
require_once 'Zend/Service/Abstract.php';

/**
 * @see Zend_Json_Decoder
 */
require_once 'Zend/Json/Decoder.php';

class Zend_Service_CloudFlare extends Zend_Service_Abstract
{
    /**
     * Base URI for All POSTs should be directed at the host provider gateway interface
     */
    const URI_BASE = 'https://api.cloudflare.com/host-gw.html';

    /**
     * Host Providers who have been granted access to the API are issued a host_key key.
     * These keys are 32 characters in length and may be locked to a particular set of IP addresses.
     *
     * @var string
     */
    protected $_apiKey;

    /**
     * Period after which HTTP request will timeout in seconds
     */
    protected $_httpTimeout = 10;

    /**
     * Constructor
     *
     * @param string $apiKey Host Providers API key
     *
     * @return void
     */
    public function __construct($apiKey)
    {
        $this->_apiKey = (string) $apiKey;
    }

    /*
     * Implements everything else like as Object#method_missing of Ruby
     * Any method not defined explicitly will be passed on to the CloudFlare API,
     * and return as json, but it works only PHP 5.3.0+.
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return array
     */
    public function __call($name, $arguments)
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
			throw new Zend_Service_CloudFlare_Exception('Oops! o_O Tried to call undefined method using __call(). But PHP version is required PHP5.3.0+');
        }

        $act     = $this->_camelcase2Underscored($name);;
        $options = $this->_prepareOptions($act, $options);
        return $this->_post($options);
    }

     /*
      * userCreate - Create a CloudFlare account mapped to your user (required)
      *
      * @params array $options should include unless optional
      * - cloudflare_email    : required
      * - cloudflare_pass     : required
      * - cloudflare_username : optional
      * - unique_id           : optional
      * - clobber_unique_id   : optional
      *
      * @throws Zend_Http_Client_Exception if HTTP request fails or times out
      * @throws Zend_Json_Exception if decoding is fails
      * @return array
      */
    public function userCreate(array $options = array())
    {
        $act     = 'user_create';
        $options = $this->_prepareOptions($act, $options);
        return $this->_post($options);
    }

     /*
      * zoneSet - Setup a User's zone for CNAME hosting (required)
      *
      * @params array $options should include unless optional
      * - user_key   : required
      * - zone_name  : required
      * - resolve_to : required
      * - subdomains : required
      *
      * @throws Zend_Http_Client_Exception if HTTP request fails or times out
      * @throws Zend_Json_Exception if decoding is fails
      * @return array
      */
    public function zoneSet(array $options = array())
    {
        $act     = 'zone_set';
        $options = $this->_prepareOptions($act, $options);
        return $this->_post($options);
    }

     /*
      * userLookup - Lookup a user's CloudFlare account information (optional)
      *
      * @params array $options should include unless optional
      * - cloudflare_email or unique_id : required
      *
      * @throws Zend_Http_Client_Exception if HTTP request fails or times out
      * @throws Zend_Json_Exception if decoding is fails
      * @return array
      */
    public function userLookup(array $options = array())
    {
        $act     = 'user_lookup';
        $options = $this->_prepareOptions($act, $options);
        return $this->_post($options);
    }

    /*
     * userAuth - Authorize access to a user's existing CloudFlare account (optional)
     *
     * @params array $options should include unless optional
     * - cloudflare_email  : required
     * - cloudflare_pass   : required
     * - unique_id         : optional
     * - clobber_unique_id : optional
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Zend_Json_Exception if decoding is fails
     * @return array
     */
    public function userAuth(array $options = array())
    {
        $act     = 'user_auth';
        $options = $this->_prepareOptions($act, $options);
        return $this->_post($options);
    }

    /*
     * zoneLookup - lookup a specific user's zone (optional)
     *
     * @params array $options should include unless optional
     * - zone_name  : required
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Zend_Json_Exception if decoding is fails
     * @return array
     */
    public function zoneLookup(array $options = array())
    {
        $act     = 'zone_lookup';
        $options = $this->_prepareOptions($act, $options);
        return $this->_post($options);
    }

    /*
     * zoneDelete - delete a specific zone on behalf of a user (optional)
     *
     * @params array $options should include unless optional
     * - zone_name  : required
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Zend_Json_Exception if decoding is fails
     * @return array
     */
    public function zoneDelete(array $options = array())
    {
        $act     = 'zone_delete';
        $options = $this->_prepareOptions($act, $options);
        return $this->_post($options);
    }

    /**
     * Prepare options for the request
     *
     * @param  string $act            Cloudflare Method to call
     * @param  array  $options        Options
     * @param  array  $defaultOptions Default Options
     * @return array  Merged array of user and default/required options
     */
    protected function _prepareOptions($act, array $options, array $defaultOptions = array())
    {
        $options['act']      = (string) $act;
        $options['host_key'] = $this->_apiKey;
        return array_merge($defaultOptions, $options);
    }

    /**
     * Post a request
     *
     * @param array  $params
     * @return mixed
     */
    protected function _post(array $options)
    {
        try {
            $client = self::getHttpClient();
            $client->setUri(self::URI_BASE);
            $client->setParameterPost($options);
            $client->setConfig(array(
                'timeout' => $this->_httpTimeout
            ));
            $client->setMethod(Zend_Http_Client::POST);
            $response = $client->request();
        } catch (Zend_Http_Client_Exception $e) {
            $message = 'Error occured while requesting to CloudFlare service: ' . $e->getMessage();
            throw new Zend_Http_Client_Exception($message, $e->getCode());
        }
        try {
            return Zend_Json_Decoder::decode($response->getBody());
        } catch (Zend_Json_Exception $e) {
            $message = 'Error occured while decoding response from CloudFlare service: ' . $e->getMessage();
            throw new Zend_Json_Exception($message);
        }
    }

    /**
     * Change string from camelcase to underscore
     *
     * examples
     *
     * fooBar => foo_bar
     * FooBar => foo_bar
     * foobar => foobar
     *
     * @param string $string
     * @return string
     */
    protected function camelcase2Underscored($string)
    {
        return strtolower(preg_replace('/([^A-Z])([A-Z])/', "$1_$2", $string));
    }
}
