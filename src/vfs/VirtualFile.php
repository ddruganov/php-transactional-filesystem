<?php

namespace ddruganov\TransactionalFileSystem\vfs;

final class VirtualFile
{
    public function __construct(
        private string $name,
        private string $content = '',
        private bool $isDeleted = false
    ) {
    }

    public function getName()
    {
        return $this->name;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $value, bool $append = false)
    {
        if ($append) {
            $value = $this->content . $value;
        }
        $this->content = $value;
    }

    public function isDeleted()
    {
        return $this->isDeleted;
    }

    public function delete()
    {
        $this->isDeleted = true;
    }
}
