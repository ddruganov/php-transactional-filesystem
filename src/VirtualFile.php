<?php

namespace ddruganov\TransactionFs;

final class VirtualFile extends VirtualFileSystemUnit
{
    public function __construct(
        string $name,
        private string $content
    ) {
        parent::__construct($name);
    }

    public function getContent(): string|array
    {
        return $this->content;
    }

    public function setContent(string $value)
    {
        $this->content = $value;
    }
}
