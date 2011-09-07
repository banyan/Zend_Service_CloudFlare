# Zend_Service_CloudFlare

Description
---

An easy interface to the CloudFlare Hosting Provider API for Zend Framework.

Official document is [here](https://www.cloudflare.com/docs/host-api.html).

Compatibility
---

Tested under PHP 5.3.0 With PHPUnit 3.5.15.

Quick start
---

    $apiKey     = "YOUR_API_KEY";
    $cloudFlare = new Zend_Service_CloudFlare($apiKey);

    $options = array(
        "cloudflare_email"    => "***********@sample.com",
        "cloudflare_pass"     => "*********",
        "cloudflare_username" => "sample",
    );

    $response = $cloudFlare->userCreate($options);
