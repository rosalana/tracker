<?php

namespace Rosalana\Tracker\Services\Tracker;

class Scope
{
    private array $context = [];
    private array $user = [];
    private array $links = [];
    private array $tags = [];

    public function setTag(string $key, mixed $value): void
    {
        $this->tags[$key] = $value;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setUser(array $user): void
    {
        $this->user = $user;
    }

    public function getUser(): array
    {
        return $this->user;
    }

    public function setContext(string $key, array $context): void
    {
        $this->context[$key] = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setLink(string $key, string $value): void
    {
        $this->links[$key] = $value;
    }

    public function getLinks(): array
    {
        return $this->links;
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
