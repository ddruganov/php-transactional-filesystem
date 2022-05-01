<?php

namespace ddruganov\TransactionFs\exceptions;

use Exception;

final class InvalidUsageException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
