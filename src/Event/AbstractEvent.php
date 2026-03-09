<?php
// src/Event/AbstractEvent.php

namespace DataBuilder\Event;

abstract class AbstractEvent implements EventInterface
{
    private bool $propagationStopped = false;

    public function __construct(private string $name) {}

    public function getName(): string { return $this->name; }

    public function stopPropagation(): void         { $this->propagationStopped = true; }
    public function isPropagationStopped(): bool    { return $this->propagationStopped; }
}