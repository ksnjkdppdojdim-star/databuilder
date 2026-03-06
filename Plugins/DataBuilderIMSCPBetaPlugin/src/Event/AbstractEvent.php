<?php
// src/Event/AbstractEvent.php

namespace DataBuilder\Event;

abstract class AbstractEvent implements EventInterface
{
    private bool $propagationStopped = false;
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string { return $this->name; }

    public function stopPropagation(): void         { $this->propagationStopped = true; }
    public function isPropagationStopped(): bool    { return $this->propagationStopped; }
}