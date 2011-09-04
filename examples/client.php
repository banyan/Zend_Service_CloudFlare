<?php

$path = '/path/to/ZendFramework/library';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require_once 'Zend/Service/CloudFlare.php';

$apiKey     = "YOUR_API_KEY";
$cloudFlare = new Zend_Service_CloudFlare($apiKey);

$options = array(
    "cloudflare_email"    => "***********@sample.com",
    "cloudflare_pass"     => "*********",
    "cloudflare_username" => "sample",
);

$response = $cloudFlare->userCreate($options);

var_dump($response);

/*
 * array(4) {
 *   ["request"]=>
 *   array(1) {
 *     ["act"]=>
 *     string(11) "user_create"
 *   }
 *   ["response"]=>
 *   array(5) {
 *     ["cloudflare_email"]=>
 *     string(22) "***********@sample.com"
 *     ["user_key"]=>
 *     string(32) "********************************
 *     ["unique_id"]=>
 *     NULL
 *     ["cloudflare_username"]=>
 *     string(7) "sample"
 *     ["user_api_key"]=>
 *     string(45) "*********************************************"
 *   }
 *   ["result"]=>
 *   string(7) "success"
 *   ["msg"]=>
 *   NULL
 * }
 */
