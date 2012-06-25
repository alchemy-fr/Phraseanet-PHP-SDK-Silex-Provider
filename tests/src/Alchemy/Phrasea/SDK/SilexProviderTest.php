<?php

namespace Alchemy\Phrasea\SDK;

use Guzzle\GuzzleServiceProvider;
use Silex\Application;
use Silex\Provider\MonologServiceProvider;

class SilexProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testInitialize()
    {
        $app = new Application();

        $app['phraseanet-sdk.apiKey'] = 'sdfmqlsdkfm';
        $app['phraseanet-sdk.apiSecret'] = 'eoieep';
        $app['phraseanet-sdk.apiUrl'] = 'https://bidule.net';

        $app->register(new PhraseanetSDKServiceProvider());
        $app->register(new MonologServiceProvider());
        $app->register(new GuzzleServiceProvider());
    }
}
