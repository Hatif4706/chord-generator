<?php

namespace App\Services;

class TreeNode
{
    public string $name;
    public array $children = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function addChild(string $key, mixed $node): void
    {
        $this->children[$key] = $node;
    }

    public function getChild(string $key): mixed
    {
        return $this->children[$key] ?? null;
    }

    public function hasChild(string $key): bool
    {
        return isset($this->children[$key]);
    }
}
