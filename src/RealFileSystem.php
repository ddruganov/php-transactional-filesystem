<?php

namespace ddruganov\TransactionFs;

use ddruganov\TransactionFs\helpers\PathHelper;

final class RealFileSystem implements FileSystemInterface
{
    # File

    public function fileExists(string $path): bool
    {
        return file_exists($this->preparePath($path));
    }

    public function readFile(string $path): ?string
    {
        $path = $this->preparePath($path);
        return file_exists($path)
            ? file_get_contents($path)
            : null;
    }

    public function writeFile(string $path, string $content, bool $append = false): bool
    {
        $path = $this->preparePath($path);
        if (!file_exists($path)) {
            $this->createFolder(PathHelper::getFolder($path));
        }
        return file_put_contents($path, $content, ($append ? FILE_APPEND : 0)) !== false;
    }

    public function deleteFile(string $path): bool
    {
        $path = $this->preparePath($path);
        return file_exists($path) && unlink($path);
    }

    # Folder

    public function folderExists(string $path): bool
    {
        return file_exists($this->preparePath($path));
    }

    public function readFolder(string $path): ?array
    {
        $path = $this->preparePath($path);
        return file_exists($path)
            ? array_values(array_filter(scandir($path), fn (string $file) => !in_array($file, ['.', '..'])))
            : null;
    }

    public function createFolder(string $path): bool
    {
        $path = $this->preparePath($path);
        return file_exists($path) || mkdir($path, recursive: true);
    }

    public function deleteFolder(string $path): bool
    {
        $path = $this->preparePath($path);
        return file_exists($path) && unlink($path);
    }

    private function preparePath(string $path): string
    {
        return DIRECTORY_SEPARATOR . PathHelper::normalize($path);
    }
}
