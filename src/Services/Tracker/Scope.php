<?php

namespace Rosalana\Tracker\Services\Tracker;

class Scope
{
    private array $tags = [];
    private array $user = [];
    private array $context = [];
    private array $links = [];

    public function setTag(string $key, mixed $value): void
    {
        $this->tags[$key] = $value;
    }

    public function setUser(array $user): void
    {
        $this->user = $user;
    }

    public function setContext(string $key, array $context): void
    {
        $this->context[$key] = $context;
    }

    public function setLink(string $key, string $value): void
    {
        $this->links[$key] = $value;
    }

    public function configure(\Closure $callback): void
    {
        $callback($this);
    }

    public function snapshot(): array
    {
        return [
            'tags' => $this->tags,
            'user' => $this->user,
            'context' => $this->context,
            'links' => $this->links,
        ];
    }
}
