<?php

namespace ddruganov\TransactionFs;

interface FileSystemInterface
{
    # File
    public function fileExists(string $path): bool;
    public function readFile(string $path): ?string;
    public function writeFile(string $path, string $content, bool $append = false): bool;
    public function deleteFile(string $path): bool;

    # Folder
    public function folderExists(string $path): bool;
    public function readFolder(string $path): ?array;
    public function createFolder(string $path): bool;
    public function deleteFolder(string $path): bool;
}
