<?php

@unlink("./warmer.phar");

// create with alias "project.phar"
$phar = new Phar('./warmer.phar', 0, 'warmer.phar');

// Will exclude everything under these directories relative to the root
$exclude_folders = array('.git', '.vscode', "conf", "var");

// add full path to the excluded folders
foreach ($exclude_folders as &$e)
{
    $e = __DIR__ . DIRECTORY_SEPARATOR . $e;
}


var_dump($exclude_folders);

// list of excluded file types
$exclude_files = [".gliffy", ".png", ".pem", ".phar"];

/**
 * @param SplFileInfo $file
 * @param mixed $key
 * @param RecursiveCallbackFilterIterator $iterator
 * @return bool True if you need to recurse or if the item is acceptable
 */
$filter = function ($file, $key, $iterator) use ($exclude_folders, $exclude_files) {
    
    $file_name = $file->getFilename();
  
    if ( ($iterator->isDir() || substr($file_name, 0, 1) == ".")) {
        if (!in_array(trim($file->getRealPath()), $exclude_folders)) {
            echo "Adding Directory: '". $file->getRealPath() ."'". PHP_EOL;
            return true;
        }
        else
        {
            echo "Skip Directory: '". $file->getRealPath() ."'". PHP_EOL;
            return false;
        }
    }
	else
	{
        foreach($exclude_files as $exclude_file)
        {
            if (stristr( $file_name, $exclude_file) !== FALSE)
            {
                echo "Skip File:".  $file_name . PHP_EOL;
                return false;
            }
        } 
		
	}

    echo "Adding File: ". $file->getFilename() . PHP_EOL; 
    return $file->isFile();
};

$innerIterator = new RecursiveDirectoryIterator(
    __DIR__,
    RecursiveDirectoryIterator::SKIP_DOTS
);
$iterator = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator($innerIterator, $filter)
);

// Add the files
$phar->buildFromIterator($iterator, __DIR__);

// Set the starting file
$phar->setStub($phar->createDefaultStub('./application.php'));
