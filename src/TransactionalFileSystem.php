<?php

namespace ddruganov\TransactionalFileSystem;

use ddruganov\TransactionalFileSystem\common\FileSystemInterface;
use ddruganov\TransactionalFileSystem\common\FileSystemUnitStatus;
use ddruganov\TransactionalFileSystem\helpers\PathHelper;
use ddruganov\TransactionalFileSystem\vfs\VirtualFile;
use ddruganov\TransactionalFileSystem\vfs\VirtualFileSystem;
use ddruganov\TransactionalFileSystem\vfs\VirtualFolder;

final class TransactionalFileSystem implements FileSystemInterface
{
    private VirtualFileSystem $virtualFileSystem;
    private RealFileSystem $realFileSystem;

    public function __construct()
    {
        $this->virtualFileSystem = new VirtualFileSystem();
        $this->realFileSystem = new RealFileSystem();
    }

    # Transactional features

    public function commit(): bool
    {
        if (!$this->virtualFileSystem->hasChanges()) {
            return true;
        }

        $namelessRoot = new VirtualFolder('');
        $namelessRoot->copyFrom($this->virtualFileSystem->getRoot());
        return $this->commitFolder($namelessRoot);
    }

    private function commitFolder(VirtualFolder $virtualFolder, string $aggregatePath = '')
    {
        $aggregatePath = PathHelper::concat($aggregatePath, $virtualFolder->getName());

        if ($virtualFolder->isDeleted()) {
            return $this->realFileSystem->getFolderStatus($aggregatePath) !== FileSystemUnitStatus::EXISTS
                || $this->realFileSystem->deleteFolder($aggregatePath);
        }

        $this->realFileSystem->getFolderStatus($aggregatePath) !== FileSystemUnitStatus::EXISTS && $this->realFileSystem->createFolder($aggregatePath);
        foreach ($virtualFolder->getFiles() as $file) {
            $filepath = PathHelper::concat($aggregatePath, $file->getName());
            if (!$this->commitFile($file, $filepath)) {
                return false;
            }
        }

        foreach ($virtualFolder->getSubfolders() as $subfolder) {
            if (!$this->commitFolder($subfolder, $aggregatePath)) {
                return false;
            }
        }

        return true;
    }

    private function commitFile(VirtualFile $file, string $path)
    {
        if ($file->isDeleted()) {
            return $this->realFileSystem->deleteFile($path);
        }

        return $this->realFileSystem->writeFile($path, $file->getContent());
    }

    public function rollback()
    {
        $this->virtualFileSystem = new VirtualFileSystem();
        return true;
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
