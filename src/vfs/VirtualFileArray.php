<?php

namespace ddruganov\TransactionalFileSystem\vfs;

use ddruganov\TransactionalFileSystem\vfs\VirtualFile;
use ddruganov\TypedArray\TypedArray;
use ddruganov\TypedArray\TypeDescription;

final class VirtualFileArray extends TypedArray
{
    public function __construct()
    {
        parent::__construct(TypeDescription::of(VirtualFile::class));
    }

    public function offsetGet(mixed $offset): VirtualFile
    {
        return parent::offsetGet($offset);
    }
}
