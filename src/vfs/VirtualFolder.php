<?php

namespace ddruganov\TransactionalFileSystem\vfs;

use ddruganov\TransactionalFileSystem\vfs\VirtualFileArray;
use ddruganov\TransactionalFileSystem\vfs\VirtualFolderArray;
use ddruganov\TransactionalFileSystem\helpers\PathHelper;

final class VirtualFolder
{
    private string $name;
    private bool $isDeleted;
    private VirtualFileArray $files;
    private VirtualFolderArray $subfolders;

    public function __construct(string $name, bool $isDeleted = false)
    {
        $this->name = $name;
        $this->isDeleted = $isDeleted;
        $this->files = new VirtualFileArray();
        $this->subfolders = new VirtualFolderArray();
    }

    public function getName()
    {
        return $this->name;
    }

    public function isDeleted()
    {
        return $this->isDeleted;
    }

    public function delete()
    {
        $this->isDeleted = true;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getSubfolders()
    {
        return $this->subfolders;
    }

    private function setSubfolder(VirtualFolder $subfolder)
    {
        $this->subfolders[$subfolder->getName()] = $subfolder;
    }

    private function setFile(VirtualFile $file)
    {
        $this->files[$file->getName()] = $file;
    }

    public function getContent(): array
    {
        $output = [];

        /** @var VirtualFile $file */
        foreach ($this->getFiles() as $file) {
            if ($file->isDeleted()) {
                continue;
            }
            $output[] = $file->getName();
        }

        /** @var VirtualFolder $subfolder */
        foreach ($this->getSubfolders() as $subfolder) {
            if ($subfolder->isDeleted()) {
                continue;
            }
            $output[] = $subfolder->getName();
        }

        return $output;
    }

    # File

    public function findFile(string $path): ?VirtualFile
    {
        $path = PathHelper::removePrefix($this->getName(), $path);
        [$head, $tail] = PathHelper::getPathHeadAndTail($path);

        if (!$tail) {
            return $this->getFiles()[$head] ?? null;
        }

        $subfolder = $this->getSubfolders()[$head] ?? null;
        return $subfolder?->findFile($tail);
    }

    public function writeFile(string $path, string $content): bool
    {
        $path = PathHelper::removePrefix($this->getName(), $path);
        [$head, $tail] = PathHelper::getPathHeadAndTail($path);

        $subfolder = $this->getSubfolders()[$head] ?? null;

        if (!$tail) {
            if ($subfolder) {
                return false;
            }

            $file = $this->getFiles()[$head] ?? new VirtualFile($head);
            $file->setContent($content);
            $this->setFile($file);
            return true;
        }

        if (!$subfolder) {
            return false;
        }

        return $subfolder->writeFile($tail, $content);
    }

    public function deleteFile(string $path): bool
    {
        $path = PathHelper::removePrefix($this->getName(), $path);
        [$head, $tail] = PathHelper::getPathHeadAndTail($path);

        $subfolder = $this->getSubfolders()[$head] ?? null;

        if (!$tail) {
            if ($subfolder) {
                return false;
            }

            $file = $this->getFiles()[$head] ?? new VirtualFile($head);
            $file->delete();
            $this->setFile($file);
            return true;
        }

        if (!$subfolder) {
            return false;
        }

        return $subfolder->deleteFile($tail);
    }

    # Folder

    public function findFolder(string $path): ?VirtualFolder
    {
        $path = PathHelper::removePrefix($this->getName(), $path);
        [$head, $tail] = PathHelper::getPathHeadAndTail($path);

        $subfolder = $this->getSubfolders()[$head] ?? null;
        return $tail ? $subfolder?->findFolder($tail) : $subfolder;
    }

    public function createFolder(string $path): bool
    {
        $path = PathHelper::removePrefix($this->getName(), $path);
        [$head, $tail] = PathHelper::getPathHeadAndTail($path);

        $subfolder = $this->getSubfolders()[$head] ?? new VirtualFolder($head);
        $this->setSubfolder($subfolder);
        return !$tail || $subfolder->createFolder($tail);
    }

    public function deleteFolder(string $path): bool
    {
        $path = PathHelper::removePrefix($this->getName(), $path);
        [$head, $tail] = PathHelper::getPathHeadAndTail($path);

        $subfolder = $this->getSubfolders()[$head] ?? new VirtualFolder($head);

        if ($tail) {
            return $subfolder->deleteFolder($tail);
        }

        $subfolder->delete();
        foreach ($subfolder->getSubfolders() as $v) {
            $subfolder->deleteFolder($v->getName());
        }
        $this->setSubfolder($subfolder);
        return true;
    }
}
