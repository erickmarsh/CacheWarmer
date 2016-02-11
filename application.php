<?php
// application.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__.'/vendor/autoload.php';

// set the working directory only if we are not running in a phar file
if (is_null(Phar::running()))
{
	chdir( __DIR__ );
}

use Lyonscg\Commands;
use Symfony\Component\Console\Application;
use Symfony\Component\Debug\DebugClassLoader;
use Symfony\Component\Debug\Debug;

//Debug::enable();
//DebugClassLoader::enable();

$application = new Application();
$application->add(new Commands\SitemapCommand());
$application->add(new Commands\GaCommand());
$application->add(new Commands\FileCommand());
$application->run();