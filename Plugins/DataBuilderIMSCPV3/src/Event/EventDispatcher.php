<?php

namespace DataBuilder\Event;

/**
 * Dispatcher PSR-14 léger.
 *
 * Usage :
 *   $dispatcher->on('block.render.before', function(BlockEvent $e) {
 *       $e->getBlock()->setData('injected', true);
 *   });
 *
 *   $event = $dispatcher->dispatch(new BlockEvent('block.render.before', $block));
 */
class EventDispatcher
{
    private ListenerProvider $provider;

    public function __construct(?ListenerProvider $provider = null)
    {
        $this->provider = $provider ?? new ListenerProvider();
    }

    /**
     * Dispatch un événement — appelle tous les listeners dans l'ordre de priorité.
     * Retourne l'événement (potentiellement modifié par les listeners).
     */
    public function dispatch(EventInterface $event): EventInterface
    {
        foreach ($this->provider->getListenersForEvent($event->getName()) as $listener) {
            if ($event->isPropagationStopped()) break;
            $listener($event);
        }

        return $event;
    }

    /**
     * Raccourci pour enregistrer un listener.
     */
    public function on(string $eventName, callable $listener, int $priority = 0): void
    {
        $this->provider->on($eventName, $listener, $priority);
    }

    public function getProvider(): ListenerProvider
    {
        return $this->provider;
    }
}