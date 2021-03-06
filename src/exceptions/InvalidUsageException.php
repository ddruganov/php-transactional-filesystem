<?php

namespace ddruganov\TransactionalFileSystem\exceptions;

use Exception;

final class InvalidUsageException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
