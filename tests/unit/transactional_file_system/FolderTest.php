<?php

namespace tests\unit\transactional_file_system;

use ddruganov\TransactionalFileSystem\exceptions\FolderNotFoundException;
use ddruganov\TransactionalFileSystem\common\FileSystemUnitStatus;
use ddruganov\TransactionalFileSystem\TransactionalFileSystem;

final class FolderTest extends TransactionalFileSystemTest
{
    public function testRead()
    {
        $transactionalFileSystem = new TransactionalFileSystem();
        $result = $transactionalFileSystem->readFolder($this->getFolder());
        $this->assertNotEmpty($result);
        $this->assertCount(1, $result);
    }

    public function testCreate()
    {
        $transactionalFileSystem = new TransactionalFileSystem();
        $result = $transactionalFileSystem->createFolder($this->getFolder());
        $this->assertTrue($result);

        $exceptionThrown = false;
        try {
            $transactionalFileSystem->readFolder($this->getFolder());
        } catch (FolderNotFoundException) {
            $exceptionThrown = true;
        }
        $this->assertFalse($exceptionThrown);
    }

    public function testDelete()
    {
        $folderToDelete = $this->getFolder();
        $folderToCreate = $this->getNestedFolder();

        $transactionalFileSystem = new TransactionalFileSystem();
        $transactionalFileSystem->createFolder($folderToCreate);
        $result = $transactionalFileSystem->deleteFolder($folderToDelete);
        $this->assertTrue($result);

        foreach ([$folderToDelete, $folderToCreate] as $path) {
            $exceptionThrown = false;
            try {
                $transactionalFileSystem->readFolder($path);
            } catch (FolderNotFoundException) {
                $exceptionThrown = true;
            }
            $this->assertTrue($exceptionThrown, $path);
        }
    }

    public function testStatus()
    {
        $transactionalFileSystem = new TransactionalFileSystem();

        $status = $transactionalFileSystem->getFolderStatus($this->getFolder());
        $this->assertTrue($status === FileSystemUnitStatus::NOT_FOUND);

        $transactionalFileSystem->createFolder($this->getFolder());
        $status = $transactionalFileSystem->getFolderStatus($this->getFolder());
        $this->assertTrue($status === FileSystemUnitStatus::EXISTS);

        $transactionalFileSystem->deleteFolder($this->getFolder());
        $status = $transactionalFileSystem->getFolderStatus($this->getFolder());
        $this->assertTrue($status === FileSystemUnitStatus::DELETED);
    }
}
