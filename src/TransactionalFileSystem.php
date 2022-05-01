<?php

namespace ddruganov\TransactionFs;

use ddruganov\TransactionFs\exceptions\UnsupportedException;

final class TransactionalFileSystem implements FileSystemInterface
{
    private FileSystemInterface $virtualFileSystem;
    private FileSystemInterface $realFileSystem;

    public function __construct()
    {
        $this->virtualFileSystem = new VirtualFileSystem();
        $this->realFileSystem = new RealFileSystem();
    }

    public function commit()
    {
        /** @var VirtualFileSystem */
        $vfs = $this->virtualFileSystem;
        foreach ($vfs->getRoot()->getChildren() as $child) {
            $this->commitUnit($child, '');
        }
    }

    private function commitUnit(VirtualFileSystemUnit $vfsUnit, string $path)
    {
        $vfsUnitPath = join(DIRECTORY_SEPARATOR, [$path, $vfsUnit->getName()]);

        if ($vfsUnit->isDeleted()) {
            if ($vfsUnit->is(VirtualFile::class)) {
                $this->realFileSystem->deleteFile($vfsUnitPath);
            }
            if ($vfsUnit->is(VirtualFolder::class)) {
                $this->realFileSystem->deleteFolder($vfsUnitPath);
            }
            return;
        }

        if ($vfsUnit->is(VirtualFile::class)) {
            $this->realFileSystem->writeFile($vfsUnitPath, $vfsUnit->getContent());
            return;
        }

        /** @var VirtualFolder $vfsUnit */
        if ($vfsUnit->is(VirtualFolder::class)) {
            foreach ($vfsUnit->getChildren() as $child) {
                $this->commitUnit($child, $vfsUnitPath);
            }
            return;
        }

        throw new UnsupportedException('virtual file system only operates with files and folders');
    }

    public function rollback()
    {
    }

    # File

    public function fileExists(string $path): bool
    {
        return
            $this->virtualFileSystem->fileExists($path)
            || $this->realFileSystem->fileExists($path);
    }

    public function readFile(string $path): ?string
    {
        return
            $this->virtualFileSystem->readFile($path)
            ?? $this->realFileSystem->readFile($path);
    }

    public function writeFile(string $path, string $content, bool $append = false): bool
    {
        if ($append && !$this->virtualFileSystem->fileExists($path)) {
            $initialContent = $this->realFileSystem->readFile($path);
            $this->virtualFileSystem->writeFile($path, $initialContent);
        }
        return $this->virtualFileSystem->writeFile($path, $content, $append);
    }

    public function deleteFile(string $path): bool
    {
        return $this->virtualFileSystem->deleteFile($path);
    }

    # Folder

    public function folderExists(string $path): bool
    {
        return
            $this->virtualFileSystem->folderExists($path)
            || $this->realFileSystem->folderExists($path);
    }

    public function readFolder(string $path): ?array
    {
        return
            $this->virtualFileSystem->readFolder($path)
            ?? $this->realFileSystem->readFolder($path);
    }

    public function createFolder(string $path): bool
    {
        if ($this->virtualFileSystem->folderExists($path)) {
            return true;
        }

        $folderCreationResult = $this->virtualFileSystem->createFolder($path);
        $contents = $this->realFileSystem->readFolder($path);
        foreach ($contents as $unitName) {
            $fullpath = join(DIRECTORY_SEPARATOR, [$path, $unitName]);
            if (is_file($fullpath)) {
                codecept_debug("$fullpath is file");
                $this->virtualFileSystem->writeFile($fullpath, $this->realFileSystem->readFile($fullpath));
            } elseif (is_dir($fullpath)) {
                codecept_debug("$fullpath is dir");
                $this->virtualFileSystem->createFolder($fullpath);
            } else {
                throw new UnsupportedException("'$fullpath' is neither a file nor a folder");
            }
        }

        return $folderCreationResult;
    }

    public function deleteFolder(string $path): bool
    {
        return $this->virtualFileSystem->deleteFolder($path);
    }
}
