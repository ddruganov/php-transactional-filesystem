<?php

namespace ddruganov\TransactionFs;

use ddruganov\TransactionFs\helpers\PathHelper;

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

    public function fileExists(string $path): bool
    {
        return $this->root->fileExists($this->preparePath($path));
    }

    public function readFile(string $path): ?string
    {
        $file = $this->root->readFile($this->preparePath($path));
        if (!$file || $file->isDeleted()) {
            return null;
        }
        return $file->getContent();
    }

    public function writeFile(string $path, string $content, bool $append = false): bool
    {
        return $this->root->writeFile($this->preparePath($path), $content, $append);
    }

    public function deleteFile(string $path): bool
    {
        $file = $this->root->readFile($path);
        if (!$file) {
            $this->root->createFile($path);
        }

        return $this->root->deleteFile($this->preparePath($path));
    }

    # Folder

    public function folderExists(string $path): bool
    {
        return $this->root->folderExists($this->preparePath($path));
    }

    public function readFolder(string $path): ?array
    {
        $folder = $this->root->readFolder($this->preparePath($path));
        if (!$folder || $folder->isDeleted()) {
            return null;
        }
        return $folder->getContent();
    }

    public function createFolder(string $path): bool
    {
        return $this->root->createFolder($this->preparePath($path));
    }

    public function deleteFolder(string $path): bool
    {
        $folder = $this->root->readFolder($path);
        if (!$folder) {
            $this->root->createFolder($path);
        }

        return $this->root->deleteFolder($this->preparePath($path));
    }

    private function preparePath(string $path)
    {
        return PathHelper::normalize(
            PathHelper::addPrefix(self::ROOT_FOLDER_NAME, $path)
        );
    }
}
