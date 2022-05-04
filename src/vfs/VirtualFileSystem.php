<?php

namespace ddruganov\TransactionalFileSystem\vfs;

use ddruganov\TransactionalFileSystem\exceptions\FileNotFoundException;
use ddruganov\TransactionalFileSystem\exceptions\FolderNotFoundException;
use ddruganov\TransactionalFileSystem\common\FileSystemInterface;
use ddruganov\TransactionalFileSystem\common\FileSystemUnitStatus;
use ddruganov\TransactionalFileSystem\helpers\PathHelper;

final class VirtualFileSystem implements FileSystemInterface
{
    private const ROOT_FOLDER_NAME = '#';

    private VirtualFolder $root;

    public function __construct()
    {
        $this->root = new VirtualFolder(self::ROOT_FOLDER_NAME);
    }

    public function getRoot(): VirtualFolder
    {
        return $this->root;
    }

    # File

    public function getFileStatus(string $path): FileSystemUnitStatus
    {
        $file = $this->root->findFile($this->preparePath($path));
        if ($file === null) {
            return FileSystemUnitStatus::NOT_FOUND;
        }

        if ($file->isDeleted()) {
            return FileSystemUnitStatus::DELETED;
        }

        return FileSystemUnitStatus::EXISTS;
    }

    public function readFile(string $path): ?string
    {
        $file = $this->root->findFile($this->preparePath($path));
        if ($file === null || $file->isDeleted()) {
            throw new FileNotFoundException($path);
        }
        return $file->getContent();
    }

    public function writeFile(string $path, string $content = '', bool $append = false): bool
    {
        if ($append) {
            $content = $this->readFile($path) . $content;
        }

        return $this->root->writeFile($this->preparePath($path), $content);
    }

    public function deleteFile(string $path): bool
    {
        return $this->root->deleteFile($this->preparePath($path));
    }

    # Folder

    public function getFolderStatus(string $path): FileSystemUnitStatus
    {
        $folder = $this->root->findFolder($this->preparePath($path));
        if ($folder === null) {
            return FileSystemUnitStatus::NOT_FOUND;
        }

        if ($folder->isDeleted()) {
            return FileSystemUnitStatus::DELETED;
        }

        return FileSystemUnitStatus::EXISTS;
    }

    public function readFolder(string $path): ?array
    {
        $folder = $this->root->findFolder($this->preparePath($path));
        if ($folder === null || $folder->isDeleted()) {
            throw new FolderNotFoundException($path);
        }
        return $folder->getContent();
    }

    public function createFolder(string $path): bool
    {
        return $this->root->createFolder($this->preparePath($path));
    }

    public function deleteFolder(string $path): bool
    {
        return $this->root->deleteFolder($this->preparePath($path));
    }

    private function preparePath(string $path)
    {
        return PathHelper::normalize(
            PathHelper::addPrefix(self::ROOT_FOLDER_NAME, $path)
        );
    }
}
