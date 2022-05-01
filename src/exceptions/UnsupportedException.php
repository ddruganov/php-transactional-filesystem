<?php

namespace ddruganov\TransactionFs\exceptions;

use Exception;

final class UnsupportedException extends Exception
{
    public function __construct(string $feature)
    {
        parent::__construct("This feature is not supported: $feature");
    }
}
