<?php

namespace tests\unit;

final class FileTest extends BaseTest
{
    private const ORIGINAL_MESSAGE = 'hello!';

    protected function setUp(): void
    {
        parent::setUp();
        file_put_contents($this->getFilename(), self::ORIGINAL_MESSAGE);
    }

    private function getFilename()
    {
        return __DIR__ . '/../data/test.txt';
    }

    private function getNonExistentFilename()
    {
        return __DIR__ . '/../data/test2.txt';
    }

    public function testReadFileWithoutPreceedingWrite()
    {
        $content = $this->transactionalFs->readFile($this->getFilename());
        $this->assertTrue($content === self::ORIGINAL_MESSAGE);
    }

    public function testReadWriteFileWithoutAppend()
    {
        $message = 'different message';
        $writeResult = $this->transactionalFs->writeFile($this->getFilename(), $message);
        $this->assertTrue($writeResult);

        $readResult = $this->transactionalFs->readFile($this->getFilename());
        $this->assertTrue($readResult !== self::ORIGINAL_MESSAGE);
        $this->assertTrue($readResult === $message);
    }

    public function testReadWriteFileWithAppend()
    {
        $message = 'different message';
        $writeResult = $this->transactionalFs->writeFile($this->getFilename(), $message, append: true);
        $this->assertTrue($writeResult);

        $readResult = $this->transactionalFs->readFile($this->getFilename());
        $this->assertTrue($readResult === (self::ORIGINAL_MESSAGE . $message));
    }

    public function testFileExists()
    {
        $this->assertTrue(
            $this->transactionalFs->fileExists($this->getFilename())
        );
        $this->assertFalse(
            $this->transactionalFs->fileExists($this->getNonExistentFilename())
        );
    }

    public function testDelete()
    {
        // both will return true because if a virtual file doesnot exist but needs to be deleted, the system will create it and immediately delete it
        // so that when commiting the system will know to delete the file even it has not been modified
        $this->assertTrue(
            $this->transactionalFs->deleteFile($this->getNonExistentFilename())
        );
        $this->assertTrue(
            $this->transactionalFs->deleteFile($this->getFilename())
        );
    }

    public function testCreateOverwriteAndDelete()
    {
        $filename = $this->getNonExistentFilename();

        $this->transactionalFs->writeFile($filename, 'line1');
        $this->transactionalFs->writeFile($filename, PHP_EOL, true);
        $this->transactionalFs->writeFile($filename, 'line2', true);

        $this->assertTrue(
            $this->transactionalFs->readFile($filename) === join(PHP_EOL, ['line1', 'line2'])
        );

        $this->transactionalFs->deleteFile($filename);

        $this->assertNull(
            $this->transactionalFs->readFile($filename)
        );
    }

    public function testWriteFileCommit()
    {
        $filename = $this->getNonExistentFilename();

        $this->transactionalFs->writeFile($filename, 'line1');
        $this->transactionalFs->writeFile($filename, PHP_EOL, true);
        $this->transactionalFs->writeFile($filename, 'line2', true);

        $this->transactionalFs->commit();

        $this->assertNotNull(
            file_get_contents($filename)
        );

        file_exists($filename) && unlink($filename);
    }

    public function testDeleteFileCommit()
    {
        $filename = $this->getNonExistentFilename();
        file_put_contents($filename, 'test');

        $this->transactionalFs->deleteFile($filename);

        $this->transactionalFs->commit();

        $this->assertFalse(
            file_exists($filename)
        );
    }
}
