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
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;
use Guzzle\Common\Cache\DoctrineCacheAdapter;
use Guzzle\Http\Plugin\CachePlugin as GuzzleCachePlugin;
use PhraseanetSDK\Client;
use Silex\Application;
use Silex\ServiceProviderInterface;
use PhraseanetSDK\HttpAdapter\Guzzle as Adapter;

/**
 * Phraseanet SDK Silex provider
 */
class PhraseanetSDKServiceProvider implements ServiceProviderInterface
{

    public function register(Application $app)
    {
        $app['phraseanet-sdk'] = $app->share(function() use ($app) {

                $guzzle = $app['guzzle.client'];
                /* @var $guzzle \Guzzle\Http\Client */

                $getCache = function () use ($app) {

                    if(!isset($app['phraseanet-sdk.cache'])) {
                        return new ArrayCache();
                    }

                    switch(strtolower($app['phraseanet-sdk.cache'])) {
                        case 'array':
                            return new ArrayCache();
                            break;
                        case 'memcache':
                            $memcache = new \Memcache();

                            $host = isset($app['phraseanet-sdk.memcache_host']) ?$app['phraseanet-sdk.memcache_host'] : 'localhost' ;
                            $port = isset($app['phraseanet-sdk.memcache_port']) ?$app['phraseanet-sdk.memcache_port'] :11211 ;

                            $memcache->addServer($host,$port);

                            $cache = new MemcacheCache();
                            $cache->setMemcache($memcache);

                            return $cache;
                            break;
                        case 'memcached':
                            $memcached = new \Memcached();

                            $host = isset($app['phraseanet-sdk.memcache_host']) ?$app['phraseanet-sdk.memcache_host'] : 'localhost' ;
                            $port = isset($app['phraseanet-sdk.memcache_port']) ?$app['phraseanet-sdk.memcache_port'] :11211 ;

                            $memcached->addServer($host,$port);

                            $cache = new MemcachedCache();
                            $cache->setMemcached($memcached);

                            return $cache;
                            break;
                        default:
                            throw new RuntimeException(sprintf('Cache `%s` is not supported', $app['phraseanet-sdk.cache']));
                            break;
                    }
                };

                $adapter = new DoctrineCacheAdapter($getCache());

                $lifetime = isset($app['phraseanet-sdk.cache_ttl']) ? $app['phraseanet-sdk.cache_ttl'] : 60;
                $revalidate = isset($app['phraseanet-sdk.cache_revalidate']) ? $app['phraseanet-sdk.cache_revalidate'] : null;

                $guzzle->addSubscriber(new GuzzleCachePlugin($adapter, $lifetime));

                $guzzle->addSubscriber(new CachePlugin($lifetime, $revalidate));

                $guzzle->setBaseUrl($app['phraseanet-sdk.apiUrl']);

                $logger = isset($app['monolog']) && $app['debug'] ? $app['monolog'] : null;

                $client = new Client($app['phraseanet-sdk.apiKey'], $app['phraseanet-sdk.apiSecret'], new Adapter($guzzle), $logger);

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
