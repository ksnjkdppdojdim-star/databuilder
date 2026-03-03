<?php

namespace DataBuilder\Event;

interface EventInterface
{
    public function getName(): string;

    /**
     * Permet à un listener de stopper la propagation aux listeners suivants.
     */
    public function stopPropagation(): void;
    public function isPropagationStopped(): bool;
}