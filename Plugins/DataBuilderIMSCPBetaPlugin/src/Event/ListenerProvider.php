<?php

namespace DataBuilder\Event;

/**
 * Registre des listeners par nom d'événement.
 * Compatible PSR-14.
 */
class ListenerProvider
{
    /** @var array<string, array<array{callable, int}>> */
    private $listeners = [];

    /**
     * Enregistre un listener pour un événement.
     *
     * @param string   $eventName  ex: 'block.render.before'
     * @param callable $listener
     * @param int      $priority   Plus le chiffre est élevé, plus le listener est appelé tôt
     */
    public function on(string $eventName, callable $listener, int $priority = 0): void
    {
        $this->listeners[$eventName][] = [$listener, $priority];

        // Tri par priorité décroissante
        usort($this->listeners[$eventName], function ($a, $b) { return $b[1] <=> $a[1]; });
    }

    /**
     * Retourne les listeners pour un événement donné, triés par priorité.
     *
     * @return callable[]
     */
    public function getListenersForEvent(string $eventName): array
    {
        return array_column($this->listeners[$eventName] ?? [], 0);
    }

    public function hasListeners(string $eventName): bool
    {
        return !empty($this->listeners[$eventName]);
    }

    public function remove(string $eventName, callable $listener): void
    {
        if (!isset($this->listeners[$eventName])) return;

        $this->listeners[$eventName] = array_filter(
            $this->listeners[$eventName],
            function ($entry) use ($listener) { return $entry[0] !== $listener; }
        );
    }
}