<?php

namespace tests\unit\transactional_file_system;

use ddruganov\TransactionalFileSystem\common\FileSystemUnitStatus;
use ddruganov\TransactionalFileSystem\RealFileSystem;
use ddruganov\TransactionalFileSystem\TransactionalFileSystem;
use tests\unit\BaseTest;

final class CommitTest extends BaseTest
{
    private function getRoot()
    {
        return __DIR__ . '/../../..';
    }

    private function getFolder()
    {
        return "{$this->getRoot()}/commit";
    }

    private function getFilename()
    {
        return "{$this->getRoot()}/TEST FILE WITH TEST CONTENT.txt";
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetCommitFolder();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->resetCommitFolder();
    }

    private function resetCommitFolder()
    {
        $realFileSystem = new RealFileSystem();
        $realFileSystem->getFolderStatus($this->getFolder()) === FileSystemUnitStatus::EXISTS && $realFileSystem->deleteFolder($this->getFolder());
        $realFileSystem->getFileStatus($this->getFilename()) === FileSystemUnitStatus::EXISTS && $realFileSystem->deleteFile($this->getFilename());
    }

    public function testEmpty()
    {
        $transactionalFileSystem = new TransactionalFileSystem();
        $result = $transactionalFileSystem->commit();
        $this->assertTrue($result);
    }

    public function testCreateFolder()
    {
        $transactionalFileSystem = new TransactionalFileSystem();
        $transactionalFileSystem->createFolder($this->getFolder());

        $result = $transactionalFileSystem->commit();
        $this->assertTrue($result);

        $realFileSystem = new RealFileSystem();
        $this->assertTrue($realFileSystem->getFolderStatus($this->getFolder()) === FileSystemUnitStatus::EXISTS);
    }

    public function testCreateFile()
    {
        $fileContent = 'hello!';
        $filename = 'test.txt';

        $transactionalFileSystem = new TransactionalFileSystem();
        $transactionalFileSystem->writeFile($filename, $fileContent); // writes a file to root folder

        $result = $transactionalFileSystem->commit();
        $this->assertTrue($result);

        $realFileSystem = new RealFileSystem();
        $this->assertTrue($realFileSystem->getFileStatus($filename) === FileSystemUnitStatus::EXISTS);
        $this->assertTrue($realFileSystem->readFile($filename) === $fileContent);

        $realFileSystem->deleteFile($filename);
    }

    public function testCreateFileInFolder()
    {
        $fileContent = 'hello!';

        $transactionalFileSystem = new TransactionalFileSystem();
        $transactionalFileSystem->createFolder($this->getRoot());
        $transactionalFileSystem->writeFile($this->getFilename(), $fileContent);

        $result = $transactionalFileSystem->commit();
        $this->assertTrue($result);

        $realFileSystem = new RealFileSystem();
        $this->assertTrue($realFileSystem->getFileStatus($this->getFilename()) === FileSystemUnitStatus::EXISTS);
        $this->assertTrue($realFileSystem->readFile($this->getFilename()) === $fileContent);
    }

    public function testCreateAndImmediatelyDeleteFolder()
    {
        $folder = '/opt/testfolder';

        $transactionalFileSystem = new TransactionalFileSystem();
        $transactionalFileSystem->createFolder($folder);
        $transactionalFileSystem->deleteFolder($folder);
        $this->assertTrue(
            $transactionalFileSystem->commit()
        );

        $realFileSystem = new RealFileSystem();
        $this->assertTrue($realFileSystem->getFolderStatus($folder) === FileSystemUnitStatus::NOT_FOUND);
    }

    public function testDeleteFolder()
    {
        $folder = '/opt/testfolder';

        $realFileSystem = new RealFileSystem();
        $realFileSystem->createFolder($folder);

        $transactionalFileSystem = new TransactionalFileSystem();
        $transactionalFileSystem->deleteFolder($folder);
        $this->assertTrue(
            $transactionalFileSystem->commit()
        );

        $this->assertTrue($realFileSystem->getFolderStatus($folder) === FileSystemUnitStatus::NOT_FOUND);
    }
}
