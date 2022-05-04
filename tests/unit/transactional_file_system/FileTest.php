<?php

namespace tests\unit\transactional_file_system;

use ddruganov\TransactionalFileSystem\exceptions\FileNotFoundException;
use ddruganov\TransactionalFileSystem\common\FileSystemUnitStatus;
use ddruganov\TransactionalFileSystem\TransactionalFileSystem;

final class FileTest extends TransactionalFileSystemTest
{
    public function testRead()
    {
        $transactionalFileSystem = new TransactionalFileSystem();
        $content = $transactionalFileSystem->readFile($this->getFileName());
        $this->assertTrue($content === $this->getFileContent());
    }

    public function testCreateWithoutFolder()
    {
        $transactionalFileSystem = new TransactionalFileSystem();
        $result = $transactionalFileSystem->writeFile($this->getFileName(), $this->getRandomFileContent());
        $this->assertFalse($result);

        $content = $transactionalFileSystem->readFile($this->getFileName());
        $this->assertTrue($content === $this->getFileContent());
    }

    public function testCreateWithFolder()
    {
        $transactionalFileSystem = new TransactionalFileSystem();
        $transactionalFileSystem->createFolder($this->getFolder());
        $result = $transactionalFileSystem->writeFile($this->getFileName(), $this->getFileContent());
        $this->assertTrue($result);

        $exceptionThrown = false;
        try {
            $transactionalFileSystem->readFile($this->getFileName());
        } catch (FileNotFoundException) {
            $exceptionThrown = true;
        }
        $this->assertFalse($exceptionThrown);
    }

    public function testDelete()
    {
        $transactionalFileSystem = new TransactionalFileSystem();
        $transactionalFileSystem->createFolder($this->getFolder());
        $transactionalFileSystem->writeFile($this->getFileName(), $this->getFileContent());
        $result = $transactionalFileSystem->deleteFile($this->getFileName());

        $this->assertTrue($result);

        $exceptionThrown = false;
        try {
            $transactionalFileSystem->readFile($this->getFileName());
        } catch (FileNotFoundException) {
            $exceptionThrown = true;
        }
        $this->assertTrue($exceptionThrown);
    }

    public function testStatus()
    {
        $transactionalFileSystem = new TransactionalFileSystem();

        $status = $transactionalFileSystem->getFileStatus($this->getFileName());
        $this->assertTrue($status === FileSystemUnitStatus::NOT_FOUND);

        $transactionalFileSystem->createFolder($this->getFolder());
        $transactionalFileSystem->writeFile($this->getFileName(), $this->getFileContent());
        $status = $transactionalFileSystem->getFileStatus($this->getFileName());
        $this->assertTrue($status === FileSystemUnitStatus::EXISTS);

        $transactionalFileSystem->deleteFile($this->getFileName());
        $status = $transactionalFileSystem->getFileStatus($this->getFileName());
        $this->assertTrue($status === FileSystemUnitStatus::DELETED);
    }
}
