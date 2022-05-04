<?php

namespace ddruganov\TransactionalFileSystem\vfs;

use ddruganov\TransactionalFileSystem\vfs\VirtualFolder;
use ddruganov\TypedArray\TypedArray;
use ddruganov\TypedArray\TypeDescription;

final class VirtualFolderArray extends TypedArray
{
    public function __construct()
    {
        parent::__construct(TypeDescription::of(VirtualFolder::class));
    }

    public function offsetGet(mixed $offset): VirtualFolder
    {
        return parent::offsetGet($offset);
    }
}
