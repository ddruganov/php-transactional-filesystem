<?php

namespace tests\unit\real_file_system;

use ddruganov\TransactionalFileSystem\exceptions\FolderNotFoundException;
use ddruganov\TransactionalFileSystem\common\FileSystemUnitStatus;
use ddruganov\TransactionalFileSystem\RealFileSystem;
use tests\unit\BaseTest;

final class FolderTest extends BaseTest
{
    protected function tearDown(): void
    {
        parent::tearDown();
        
        $realFileSystem = new RealFileSystem();
        $realFileSystem->getFolderStatus($this->getFolder()) === FileSystemUnitStatus::EXISTS && $realFileSystem->deleteFolder($this->getFolder());
    }

    private function getFolder()
    {
        return __DIR__ . '/../../rfs';
    }

    private function getNestedFolder()
    {
        return __DIR__ . '/../../rfs/nested';
    }

    public function testRead()
    {
        $exceptionThrown = false;
        try {
            $realFileSystem = new RealFileSystem();
            $realFileSystem->readFolder($this->getFolder());
        } catch (FolderNotFoundException) {
            $exceptionThrown = true;
        }
        $this->assertTrue($exceptionThrown);
    }

    public function testCreate()
    {
        $realFileSystem = new RealFileSystem();
        $result = $realFileSystem->createFolder($this->getFolder());
        $this->assertTrue($result);

        $exceptionThrown = false;
        try {
            $realFileSystem->readFolder($this->getFolder());
        } catch (FolderNotFoundException) {
            $exceptionThrown = true;
        }
        $this->assertFalse($exceptionThrown);
    }

    public function testDelete()
    {
        $folderToDelete = $this->getFolder();
        $folderToCreate = $this->getNestedFolder();

        $realFileSystem = new RealFileSystem();
        $realFileSystem->createFolder($folderToCreate);
        $result = $realFileSystem->deleteFolder($folderToDelete);
        $this->assertTrue($result);

        foreach ([$folderToDelete, $folderToCreate] as $path) {
            $exceptionThrown = false;
            try {
                $realFileSystem->readFolder($path);
            } catch (FolderNotFoundException) {
                $exceptionThrown = true;
            }
            $this->assertTrue($exceptionThrown, $path);
        }
    }

    public function testStatus()
    {
        $realFileSystem = new RealFileSystem();

        $status = $realFileSystem->getFolderStatus($this->getFolder());
        $this->assertTrue($status === FileSystemUnitStatus::NOT_FOUND);

        $realFileSystem->createFolder($this->getFolder());
        $status = $realFileSystem->getFolderStatus($this->getFolder());
        $this->assertTrue($status === FileSystemUnitStatus::EXISTS);

        $realFileSystem->deleteFolder($this->getFolder());
        $status = $realFileSystem->getFolderStatus($this->getFolder());
        $this->assertTrue($status === FileSystemUnitStatus::NOT_FOUND);
    }
}
