<?php

namespace ddruganov\TransactionFs;

abstract class VirtualFileSystemUnit
{
    private bool $isDeleted = false;

    public function __construct(
        private string $name
    ) {
    }

    public function isDeleted()
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $value)
    {
        $this->isDeleted = $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function is(string $className)
    {
        return is_a($this, $className, true);
    }

    public abstract function getContent(): string|array;
}
