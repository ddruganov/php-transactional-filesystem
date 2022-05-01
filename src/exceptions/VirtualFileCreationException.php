<?php

namespace ddruganov\TransactionFs\exceptions;

use Exception;

final class VirtualFileCreationException extends Exception
{
    public function __construct(string $path)
    {
        parent::__construct("Unable to create $path");
    }
}
