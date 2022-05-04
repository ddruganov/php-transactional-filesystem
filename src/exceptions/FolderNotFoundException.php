<?php

namespace ddruganov\TransactionalFileSystem\exceptions;

use Exception;

final class FolderNotFoundException extends Exception
{
    public function __construct(string $path)
    {
        parent::__construct("Folder not found: $path");
    }
}
