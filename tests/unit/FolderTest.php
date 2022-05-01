<?php

namespace tests\unit;

final class FolderTest extends BaseTest
{
    private function getFolder()
    {
        return __DIR__ . '/../data';
    }

    private function getNonExistentFolder()
    {
        return __DIR__ . '/../data/test';
    }

    public function testReadFolder()
    {
        $content = $this->transactionalFs->readFolder($this->getFolder());
        $this->assertNotEmpty($content);
        $this->assertCount(1, $content);
    }

    public function testFolderExists()
    {
        $this->assertTrue(
            $this->transactionalFs->folderExists($this->getFolder())
        );
        $this->assertFalse(
            $this->transactionalFs->folderExists($this->getNonExistentFolder())
        );
    }

    public function testFolderCreate()
    {
        $writeResult = $this->transactionalFs->createFolder($this->getFolder());
        $this->assertTrue($writeResult);

        $content = $this->transactionalFs->readFolder($this->getFolder());
        // $this->assertNotEmpty($content);
        // $this->assertCount(1, $content);

        $this->dump($content);
        $this->dump($this->transactionalFs);
    }
}
