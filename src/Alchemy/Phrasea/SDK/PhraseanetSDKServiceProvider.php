<?php

/*
 * This file is part of Phraseanet SDK Silex Provider.
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\SDK;

use Alchemy\Phrasea\SDK\Exception\RuntimeException;
use PhraseanetSDK\Client;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Phraseanet SDK Silex provider
 */
class PhraseanetSDKServiceProvider implements ServiceProviderInterface
{

    public function register(Application $app)
    {
        $app['phraseanet-sdk'] = $app->share(function() use ($app) {

                $guzzle = $app['guzzle.client'];
                $guzzle->setBaseUrl($app['phraseanet-sdk.apiUrl']);

                $client = new Client($app['phraseanet-sdk.apiKey'], $app['phraseanet-sdk.apiSecret'], $guzzle, $app['monolog']);

                if (isset($app['phraseanet-sdk.apiDevToken'])) {
                    $client->setAccessToken($app['phraseanet-sdk.apiDevToken']);
                }

                return $client;
            });
    }

    public function boot(Application $app)
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

        if ( ! isset($app['monolog'])) {
            throw new RuntimeException('Phraseanet SDK Provider requires monolog service');
        }

        if ( ! isset($app['guzzle'])) {
            throw new RuntimeException('Phraseanet SDK Provider requires guzzle service');
        }
    }
}
