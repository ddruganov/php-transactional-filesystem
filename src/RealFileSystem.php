<?php

namespace ddruganov\TransactionalFileSystem;

use ddruganov\TransactionalFileSystem\common\FileSystemInterface;
use ddruganov\TransactionalFileSystem\common\FileSystemUnitStatus;
use ddruganov\TransactionalFileSystem\exceptions\FileNotFoundException;
use ddruganov\TransactionalFileSystem\exceptions\FolderNotFoundException;
use ddruganov\TransactionalFileSystem\helpers\PathHelper;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class RealFileSystem implements FileSystemInterface
{
    # File

    public function getFileStatus(string $path): FileSystemUnitStatus
    {
        if (file_exists($this->preparePath($path))) {
            return FileSystemUnitStatus::EXISTS;
        }

        return FileSystemUnitStatus::NOT_FOUND;
    }

    public function readFile(string $path): ?string
    {
        $path = $this->preparePath($path);
        if (!file_exists($path)) {
            throw new FileNotFoundException($path);
        }
        return file_get_contents($path);
    }

    public function writeFile(string $path, string $content = '', bool $append = false): bool
    {
        $path = $this->preparePath($path);
        if (!file_exists(PathHelper::getFolder($path))) {
            return false;
        }
        return file_put_contents($path, $content, ($append ? FILE_APPEND : 0)) !== false;
    }

    public function deleteFile(string $path): bool
    {
        $path = $this->preparePath($path);
        return file_exists($path) && unlink($path);
    }

    # Folder

    public function getFolderStatus(string $path): FileSystemUnitStatus
    {
        if (file_exists($this->preparePath($path))) {
            return FileSystemUnitStatus::EXISTS;
        }

        return FileSystemUnitStatus::NOT_FOUND;
    }

    public function readFolder(string $path): ?array
    {
        $path = $this->preparePath($path);
        if (!file_exists($path)) {
            throw new FolderNotFoundException($path);
        }

        $contents = scandir($path);
        $contents = array_filter($contents, fn (string $file) => !in_array($file, ['.', '..']));
        return array_values($contents);
    }

    public function createFolder(string $path): bool
    {
        return mkdir($this->preparePath($path), recursive: true);
    }

    public function deleteFolder(string $path): bool
    {
        $path = $this->preparePath($path);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $fileinfo->isDir()
                ? rmdir($fileinfo->getRealPath())
                : unlink($fileinfo->getRealPath());
        }

        return rmdir($path);
    }

    private function preparePath(string $path): string
    {
        return DIRECTORY_SEPARATOR . PathHelper::normalize($path);
    }
}
