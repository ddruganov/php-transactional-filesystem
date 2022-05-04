<?php

namespace ddruganov\TransactionalFileSystem\exceptions;

use Exception;

final class FileNotFoundException extends Exception
{
    public function __construct(string $path)
    {
        parent::__construct("File not found: $path");
    }
}
