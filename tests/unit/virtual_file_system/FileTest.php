<?php

namespace tests\unit\vfs;

use ddruganov\TransactionalFileSystem\exceptions\FileNotFoundException;
use ddruganov\TransactionalFileSystem\common\FileSystemUnitStatus;
use ddruganov\TransactionalFileSystem\vfs\VirtualFileSystem;
use tests\unit\BaseTest;

final class FileTest extends BaseTest
{
    private function getFolder()
    {
        return __DIR__ . '/../../data';
    }

    private function getFileName()
    {
        return "{$this->getFolder()}/test.txt";
    }

    private function getFileContent()
    {
        return 'hello, this is a test string';
    }

    public function testRead()
    {
        $exceptionThrown = false;
        try {
            $virtualFileSystem = new VirtualFileSystem();
            $virtualFileSystem->readFile($this->getFileName());
        } catch (FileNotFoundException) {
            $exceptionThrown = true;
        }
        $this->assertTrue($exceptionThrown);
    }

    public function testCreateWithoutFolder()
    {
        $virtualFileSystem = new VirtualFileSystem();
        $result = $virtualFileSystem->writeFile($this->getFileName(), $this->getFileContent());
        $this->assertFalse($result);

        $exceptionThrown = false;
        try {
            $virtualFileSystem->readFile($this->getFileName());
        } catch (FileNotFoundException) {
            $exceptionThrown = true;
        }
        $this->assertTrue($exceptionThrown);
    }

    public function testCreateWithFolder()
    {
        $virtualFileSystem = new VirtualFileSystem();
        $virtualFileSystem->createFolder($this->getFolder());
        $result = $virtualFileSystem->writeFile($this->getFileName(), $this->getFileContent());
        $this->assertTrue($result);

        $exceptionThrown = false;
        try {
            $virtualFileSystem->readFile($this->getFileName());
        } catch (FileNotFoundException) {
            $exceptionThrown = true;
        }
        $this->assertFalse($exceptionThrown);
    }

    public function testDelete()
    {
        $virtualFileSystem = new VirtualFileSystem();
        $virtualFileSystem->createFolder($this->getFolder());
        $virtualFileSystem->writeFile($this->getFileName(), $this->getFileContent());
        $result = $virtualFileSystem->deleteFile($this->getFileName());

        $this->assertTrue($result);

        $exceptionThrown = false;
        try {
            $virtualFileSystem->readFile($this->getFileName());
        } catch (FileNotFoundException) {
            $exceptionThrown = true;
        }
        $this->assertTrue($exceptionThrown);
    }

    public function testStatus()
    {
        $virtualFileSystem = new VirtualFileSystem();

        $status = $virtualFileSystem->getFileStatus($this->getFileName());
        $this->assertTrue($status === FileSystemUnitStatus::NOT_FOUND);

        $virtualFileSystem->createFolder($this->getFolder());
        $virtualFileSystem->writeFile($this->getFileName(), $this->getFileContent());
        $status = $virtualFileSystem->getFileStatus($this->getFileName());
        $this->assertTrue($status === FileSystemUnitStatus::EXISTS);

        $virtualFileSystem->deleteFile($this->getFileName());
        $status = $virtualFileSystem->getFileStatus($this->getFileName());
        $this->assertTrue($status === FileSystemUnitStatus::DELETED);
    }
}
