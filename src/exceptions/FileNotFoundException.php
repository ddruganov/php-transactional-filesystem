<?php

namespace ddruganov\TransactionFs\exceptions;

use Exception;

final class FileNotFoundException extends Exception
{
    public function __construct(string $filename)
    {
        parent::__construct("File not found: $filename");
    }
}
