# Zend_Service_CloudFlare

Description
---

An easy interface to the CloudFlare Hosting Provider API for Zend Framework.

Official document is [here](https://www.cloudflare.com/docs/host-api.html).

Problems
---

No unit tests.

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

See examples/client.php also.
