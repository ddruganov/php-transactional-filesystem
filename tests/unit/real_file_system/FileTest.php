<?php

namespace tests\unit\real_file_system;

use ddruganov\TransactionalFileSystem\exceptions\FileNotFoundException;
use ddruganov\TransactionalFileSystem\common\FileSystemUnitStatus;
use ddruganov\TransactionalFileSystem\RealFileSystem;
use tests\unit\BaseTest;

final class FileTest extends BaseTest
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
            $realFileSystem = new RealFileSystem();
            $realFileSystem->readFile($this->getFileName());
        } catch (FileNotFoundException) {
            $exceptionThrown = true;
        }
        $this->assertTrue($exceptionThrown);
    }

    public function testCreateWithoutFolder()
    {
        $realFileSystem = new RealFileSystem();
        $result = $realFileSystem->writeFile($this->getFileName(), $this->getFileContent());
        $this->assertFalse($result);

        $exceptionThrown = false;
        try {
            $realFileSystem->readFile($this->getFileName());
        } catch (FileNotFoundException) {
            $exceptionThrown = true;
        }
        $this->assertTrue($exceptionThrown);
    }

    public function testCreateWithFolder()
    {
        $realFileSystem = new RealFileSystem();
        $realFileSystem->createFolder($this->getFolder());
        $result = $realFileSystem->writeFile($this->getFileName(), $this->getFileContent());
        $this->assertTrue($result);

        $exceptionThrown = false;
        try {
            $realFileSystem->readFile($this->getFileName());
        } catch (FileNotFoundException) {
            $exceptionThrown = true;
        }
        $this->assertFalse($exceptionThrown);
    }

    public function testDelete()
    {
        $realFileSystem = new RealFileSystem();
        $realFileSystem->createFolder($this->getFolder());
        $realFileSystem->writeFile($this->getFileName(), $this->getFileContent());
        $result = $realFileSystem->deleteFile($this->getFileName());

        $this->assertTrue($result);

        $exceptionThrown = false;
        try {
            $realFileSystem->readFile($this->getFileName());
        } catch (FileNotFoundException) {
            $exceptionThrown = true;
        }
        $this->assertTrue($exceptionThrown);
    }

    public function testStatus()
    {
        $realFileSystem = new RealFileSystem();

        $status = $realFileSystem->getFileStatus($this->getFileName());
        $this->assertTrue($status === FileSystemUnitStatus::NOT_FOUND);

        $realFileSystem->createFolder($this->getFolder());
        $realFileSystem->writeFile($this->getFileName(), $this->getFileContent());
        $status = $realFileSystem->getFileStatus($this->getFileName());
        $this->assertTrue($status === FileSystemUnitStatus::EXISTS);

        $realFileSystem->deleteFile($this->getFileName());
        $status = $realFileSystem->getFileStatus($this->getFileName());
        $this->assertTrue($status === FileSystemUnitStatus::NOT_FOUND);
    }
}
