<?php

namespace ddruganov\TransactionalFileSystem;

use ddruganov\TransactionalFileSystem\common\FileSystemInterface;
use ddruganov\TransactionalFileSystem\common\FileSystemUnitStatus;
use ddruganov\TransactionalFileSystem\vfs\VirtualFileSystem;

final class TransactionalFileSystem implements FileSystemInterface
{
    private FileSystemInterface $virtualFileSystem;
    private FileSystemInterface $realFileSystem;

    public function __construct()
    {
        $this->virtualFileSystem = new VirtualFileSystem();
        $this->realFileSystem = new RealFileSystem();
    }

    # File

    public function getFileStatus(string $path): FileSystemUnitStatus
    {
        return $this->virtualFileSystem->getFileStatus($path);
    }

    public function readFile(string $path): ?string
    {
        $virtualFileStatus = $this->virtualFileSystem->getFileStatus($path);
        if ($virtualFileStatus !== FileSystemUnitStatus::NOT_FOUND) {
            return $this->virtualFileSystem->readFile($path);
        }

        return $this->realFileSystem->readFile($path);
    }

    public function writeFile(string $path, string $content = '', bool $append = false): bool
    {
        if ($append) {
            $notFoundInVirtualFileSystem = $this->virtualFileSystem->getFileStatus($path) === FileSystemUnitStatus::NOT_FOUND;
            $existsInRealFileSystem = $this->realFileSystem->getFileStatus($path) === FileSystemUnitStatus::EXISTS;
            if ($notFoundInVirtualFileSystem && $existsInRealFileSystem) {
                $initialContent = $this->realFileSystem->readFile($path);
                $this->virtualFileSystem->writeFile($path, $initialContent);
            }
        }
        return $this->virtualFileSystem->writeFile($path, $content, $append);
    }

    public function deleteFile(string $path): bool
    {
        return $this->virtualFileSystem->deleteFile($path);
    }

    # Folder

    public function getFolderStatus(string $path): FileSystemUnitStatus
    {
        return $this->virtualFileSystem->getFolderStatus($path);
    }

    public function readFolder(string $path): ?array
    {
        $virtualFolderStatus = $this->virtualFileSystem->getFolderStatus($path);
        if ($virtualFolderStatus !== FileSystemUnitStatus::NOT_FOUND) {
            return $this->virtualFileSystem->readFolder($path);
        }

        return $this->realFileSystem->readFolder($path);
    }

    public function createFolder(string $path): bool
    {
        return $this->virtualFileSystem->createFolder($path);
    }

    public function deleteFolder(string $path): bool
    {
        return $this->virtualFileSystem->deleteFolder($path);
    }
}
