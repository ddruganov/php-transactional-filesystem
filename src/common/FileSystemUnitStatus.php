<?php

namespace ddruganov\TransactionalFileSystem\common;

enum FileSystemUnitStatus: int
{
    case EXISTS = 1;
    case NOT_FOUND = 2;
    case DELETED = 3;
}
