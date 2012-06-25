<?php

namespace Alchemy\Phrasea\SDK;

use Alchemy\Phrasea\SDK\Exception\RuntimeException;
use PhraseanetSDK\Client;
use Silex\Application;
use Silex\ServiceProviderInterface;

class SilexProvider implements ServiceProviderInterface
{

    public function register(Application $app)
    {
        if ( ! isset($app['phraseanet-sdk.apiSecret'])) {
            throw new RuntimeException('You must provide an api secret');
        }

        if ( ! isset($app['phraseanet-sdk.apiKey'])) {
            throw new RuntimeException('You must provide an api key');
        }

        if ( ! isset($app['phraseanet-sdk.apiUrl'])) {
            throw new RuntimeException('You must provide an api url');
        }

        $app['phraseanet-sdk'] = $app->share(function() {

                $guzzle = $app['guzzle.client'];
                $guzzle->setBaseUrl($app['phraseanet-sdk.apiUrl']);

                return  new Client($app['phraseanet-sdk.apiKey'], $app['phraseanet-sdk.apiSecret'], $guzzle, $app['monolog']);
            });
    }

    public function boot(Application $app)
    {

        if ( ! isset($app['monolog'])) {
            throw new RuntimeException('Phraseanet SDK Provider requires monolog service');
        }

        if ( ! isset($app['guzzle'])) {
            throw new RuntimeException('Phraseanet SDK Provider requires guzzle service');
        }
    }
}
