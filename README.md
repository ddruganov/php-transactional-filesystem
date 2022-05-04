# Transactional Filesystem for PHP

Virtual file system that applies changes to the real file system on commit

## Installation

`composer require ddruganov/php-transactional-filesystem`

### TODO:

-   check permissions when creating files and folder in virtual filesystem
-   record mode that files and folders are to be created with

### How-to

```php

$folder = '/opt/your-folder';
$file = "$folder/test.txt";

// work with a transaction
$transactionalFileSystem = new TransactionalFileSystem();
$transactionalFileSystem->createFolder($folder);
$transactionalFileSystem->writeFile($file, 'some string content');
$transactionalFileSystem->commit();

// check that files are actually created
$realFileSystem = new RealFileSystem();
$realFileSystem->getFolderStatus($folder); // will be FileSystemUnitStatus::EXISTS
$realFileSystem->getFileStatus($file); // will be FileSystemUnitStatus::EXISTS

// delete the file
$transactionalFileSystem = new TransactionalFileSystem();
$transactionalFileSystem->deleteFile($file);
$transactionalFileSystem->commit();

// check that files are actually deleted
$realFileSystem = new RealFileSystem();
$realFileSystem->getFolderStatus($folder); // will be FileSystemUnitStatus::EXISTS
$realFileSystem->getFileStatus($file); // will be FileSystemUnitStatus::NOT_FOUND

```
