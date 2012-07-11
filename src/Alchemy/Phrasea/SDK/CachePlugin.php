<?php

namespace Alchemy\Phrasea\SDK;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Guzzle\Common\Event;

class CachePlugin implements EventSubscriberInterface
{
    protected $cacheTTL;
    protected $revalidate;

    public function __construct($cacheTTL, $revalidate = null)
    {
        $this->cacheTTL = $cacheTTL;
        $this->revalidate = $revalidate;
    }

    public static function getSubscribedEvents()
    {
        //high priority coz it's about request configuration
        return array(
            'request.before_send' => array('onBeforeSend', 250),
        );
    }

    public function onBeforeSend(Event $event)
    {
        $request = $event['request'];
        $request->getParams()->set('cache.override_ttl', $this->cacheTTL);
        
        if ($this->revalidate) {
            $request->getParams()->set('cache.revalidate', $this->revalidate);
        }
    }
}
