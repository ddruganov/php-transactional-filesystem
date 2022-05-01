<?php

namespace ddruganov\TransactionFs;

use ddruganov\TransactionFs\exceptions\InvalidUsageException;
use ddruganov\TransactionFs\exceptions\VirtualFileCreationException;
use ddruganov\TransactionFs\helpers\PathHelper;

final class VirtualFolder extends VirtualFileSystemUnit
{
    /** @var VirtualFileSystemUnit[] */
    private array $children = [];

    public function getChildren()
    {
        return $this->children;
    }

    public function fileExists(string $path): bool
    {
        $file = $this->readFile($path);
        return $file && !$file->isDeleted();
    }

    public function readFile(string $path): ?VirtualFile
    {
        [$childName, $childTail, $pathEnded] = $this->getChildPathInfo($path);
        $child = $this->getChild($childName);

        if (!$child) {
            return null;
        }

        if ($pathEnded) {
            if (!$child->is(VirtualFile::class)) {
                throw new InvalidUsageException("Cant read file '$path' because it is not a file");
            }
            return $child;
        }

        /** @var VirtualFolder $child */
        if ($child->is(VirtualFolder::class)) {
            return $child->readFile($childTail);
        }

        throw new InvalidUsageException("File is not at the end of the path: $path");
    }

    public function folderExists(string $path): bool
    {
        $folder = $this->readFolder($path);
        return $folder && !$folder->isDeleted();
    }

    public function readFolder(string $path): ?VirtualFolder
    {
        [$childName, $childTail, $pathEnded] = $this->getChildPathInfo($path);
        $child = $this->getChild($childName);

        if (!$child) {
            return null;
        }

        if ($pathEnded) {
            if (!$child->is(VirtualFolder::class)) {
                throw new InvalidUsageException("Cant read folder '$path' because it is not a folder");
            }
            return $child;
        }

        /** @var VirtualFolder $child */
        if ($child->is(VirtualFolder::class)) {
            return $child->readFolder($childTail);
        }
        throw new InvalidUsageException("File is not at the end of the path: $path");
    }

    public function writeFile(string $path, string $content, bool $append): bool
    {
        // if (!$this->fileExists($path) && !$this->createFile($path)) {
        //     throw new VirtualFileCreationException($path);
        // }

        [$childName, $childTail, $pathEnded] = $this->getChildPathInfo($path);
        $child = $this->getChild($childName);

        if ($pathEnded) {
            if (!$child->is(VirtualFile::class)) {
                throw new InvalidUsageException("Cannot write to '$path' because it is not a file");
            }

            if ($append) {
                $content = $child->getContent() . $content;
            }
            /** @var VirtualFile $child */
            $child->setContent($content);
            return true;
        }

        if ($child->is(VirtualFile::class)) {
            throw new InvalidUsageException("File is not at the end of the path: $path");
        }

        /** @var VirtualFolder $child */
        return $child->writeFile($childTail, $content, $append);
    }

    public function createFile(string $path): bool
    {
        [$childName, $childTail, $pathEnded] = $this->getChildPathInfo($path);
        $child = $this->getChild($childName);

        if ($pathEnded) {
            if (!$child->is(VirtualFolder::class)) {
                throw new InvalidUsageException("Cannot override folder $path with a file");
            }

            $this->setChild($childName, new VirtualFile($childName, ''));
            return true;
        }

        if (!$child) {
            $childFolder = new VirtualFolder($childName);
            $folderCreationResult = $childFolder->createFile($childTail);
            $this->setChild($childName, $childFolder);
            return $folderCreationResult;
        }

        if ($child->is(VirtualFile::class)) {
            throw new InvalidUsageException("File is not at the end of the path: $path");
        }

        /** @var VirtualFolder $child */
        return $child->createFile($childTail);
    }

    public function createFolder(string $path): bool
    {
        [$childName, $childTail, $pathEnded] = $this->getChildPathInfo($path);
        $child = $this->getChild($childName);

        if (!$child) {
            $child = new VirtualFolder($childName);
            $this->setChild($childName, $child);
        }

        if (!$child->is(VirtualFolder::class)) {
            throw new InvalidUsageException("Cannot override '$path' with a folder");
        }

        if ($pathEnded) {
            return true;
        }

        /** @var VirtualFolder $child */
        return $child->createFolder($childTail);
    }

    public function deleteFile(string $path)
    {
        [$childName, $childTail, $pathEnded] = $this->getChildPathInfo($path);
        $child = $this->getChild($childName);

        if (!$child) {
            return false;
        }

        if ($pathEnded) {
            if (!$child->is(VirtualFile::class)) {
                throw new InvalidUsageException("Cannot delete file '$path' because it is not a file");
            }

            $child->setIsDeleted(true);
            $this->setChild($childName, $child);
            return true;
        }

        if ($child->is(VirtualFile::class)) {
            throw new InvalidUsageException("File is not at the end of the path: $path");
        }

        /** @var VirtualFolder $child */
        return $child->deleteFile($childTail);
    }

    private function getChild(string $childName): ?VirtualFileSystemUnit
    {
        return $this->getChildren()[$childName] ?? null;
    }

    private function setChild(string $childName, VirtualFileSystemUnit $child)
    {
        $this->children[$childName] = $child;
    }

    public function getContent(): string|array
    {
        $output = [];

        foreach ($this->getChildren() as $child) {
            if ($child->isDeleted()) {
                continue;
            }
            $output[] = $child->getName();
        }

        return $output;
    }

    private function getChildPathInfo(string $path)
    {
        $childUnitPath = PathHelper::removePrefix($this->getName(), $path);
        [$childName, $childTail] = explode('/', $childUnitPath, 2) + [1 => null];
        return [$childName, $childTail, $childTail === null];
    }
}
