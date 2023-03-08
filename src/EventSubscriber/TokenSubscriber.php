<?php
// src/EventSubscriber/TokenSubscriber.php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class TokenSubscriber implements EventSubscriberInterface
{
    private $tokens;

    public function __construct()
    {
        $this->tokens = $_ENV['API_KEY'];
    }

    public function onKernelController(ControllerEvent $event)
    {

        $token = $event->getRequest()->query->get('api_key');
        $escapeRoutes = array('/v1/bnp/credit_card_notify_response');

        if (!in_array($event->getRequest()->getRequestUri(), $escapeRoutes) && (empty($token) || $token !== $this->tokens)) {
            throw new AccessDeniedHttpException('This action needs a valid api_key!');
        }
        
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}