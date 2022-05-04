<?php

namespace tests\unit\vfs;

use ddruganov\TransactionalFileSystem\exceptions\FolderNotFoundException;
use ddruganov\TransactionalFileSystem\common\FileSystemUnitStatus;
use ddruganov\TransactionalFileSystem\vfs\VirtualFileSystem;
use tests\unit\BaseTest;

final class FolderTest extends BaseTest
{
    private function getFolder()
    {
        return __DIR__ . '/../../data';
    }

    private function getNestedFolder()
    {
        return __DIR__ . '/../../data/nested';
    }

    public function testRead()
    {
        $exceptionThrown = false;
        try {
            $virtualFileSystem = new VirtualFileSystem();
            $virtualFileSystem->readFolder($this->getFolder());
        } catch (FolderNotFoundException) {
            $exceptionThrown = true;
        }
        $this->assertTrue($exceptionThrown);
    }

    public function testCreate()
    {
        $virtualFileSystem = new VirtualFileSystem();
        $result = $virtualFileSystem->createFolder($this->getFolder());
        $this->assertTrue($result);

        $exceptionThrown = false;
        try {
            $virtualFileSystem->readFolder($this->getFolder());
        } catch (FolderNotFoundException) {
            $exceptionThrown = true;
        }
        $this->assertFalse($exceptionThrown);
    }

    public function testDelete()
    {
        $folderToDelete = $this->getFolder();
        $folderToCreate = $this->getNestedFolder();

        $virtualFileSystem = new VirtualFileSystem();
        $virtualFileSystem->createFolder($folderToCreate);
        $result = $virtualFileSystem->deleteFolder($folderToDelete);
        $this->assertTrue($result);

        foreach ([$folderToDelete, $folderToCreate] as $path) {
            $exceptionThrown = false;
            try {
                $virtualFileSystem->readFolder($path);
            } catch (FolderNotFoundException) {
                $exceptionThrown = true;
            }
            $this->assertTrue($exceptionThrown, $path);
        }
    }

    public function testStatus()
    {
        $virtualFileSystem = new VirtualFileSystem();

        $status = $virtualFileSystem->getFolderStatus($this->getFolder());
        $this->assertTrue($status === FileSystemUnitStatus::NOT_FOUND);

        $virtualFileSystem->createFolder($this->getFolder());
        $status = $virtualFileSystem->getFolderStatus($this->getFolder());
        $this->assertTrue($status === FileSystemUnitStatus::EXISTS);

        $virtualFileSystem->deleteFolder($this->getFolder());
        $status = $virtualFileSystem->getFolderStatus($this->getFolder());
        $this->assertTrue($status === FileSystemUnitStatus::DELETED);
    }
}
