<?php

namespace ddruganov\TransactionFs\exceptions;

use Exception;

final class DuplicateException extends Exception
{
    public function __construct()
    {
        parent::__construct("Duplicate found");
    }
}
