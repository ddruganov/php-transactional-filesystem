<?php

namespace tests\unit;

use ddruganov\TransactionalFileSystem\common\FileSystemUnitStatus;
use ddruganov\TransactionalFileSystem\RealFileSystem;

abstract class TransactionalFileSystemTest extends BaseTest
{
    protected function getFolder()
    {
        return __DIR__ . '/../data';
    }

    protected function getNestedFolder()
    {
        return "{$this->getFolder()}/nested";
    }

    protected function getFilename()
    {
        return "{$this->getFolder()}/test.txt";
    }

    protected function getFileContent()
    {
        return 'hello, this is a sample file text content';
    }

    protected function getRandomFileContent()
    {
        return md5(microtime());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetTestFolder();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->resetTestFolder();
    }

    private function resetTestFolder()
    {
        $realFileSystem = new RealFileSystem();
        $realFileSystem->getFolderStatus($this->getFolder()) === FileSystemUnitStatus::EXISTS && $realFileSystem->deleteFolder($this->getFolder());
        $realFileSystem->getFolderStatus($this->getFolder()) === FileSystemUnitStatus::NOT_FOUND && $realFileSystem->createFolder($this->getFolder());
        $realFileSystem->writeFile($this->getFilename(), $this->getFileContent());
    }
}
