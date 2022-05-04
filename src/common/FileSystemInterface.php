<?php

namespace ddruganov\TransactionalFileSystem\common;

interface FileSystemInterface
{
    # File
    public function getFileStatus(string $path): FileSystemUnitStatus;
    public function readFile(string $path): ?string;
    public function writeFile(string $path, string $content = '', bool $append = false): bool;
    public function deleteFile(string $path): bool;

    # Folder
    public function getFolderStatus(string $path): FileSystemUnitStatus;
    public function readFolder(string $path): ?array;
    public function createFolder(string $path): bool;
    public function deleteFolder(string $path): bool;
}
