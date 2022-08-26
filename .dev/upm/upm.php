#!/usr/bin/env php
<?php
declare(strict_types=1);

use Symfony\Component\Console\Application;

// IF this script is not being called from the CLI, THEN exit!
if (php_sapi_name() !== "cli")
    exit;

require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/../../dev/commands.php";

const UPM_COMMAND_PATH = __DIR__ . "/../../dev/Commands/UPM";
const UPM_COMMAND_FQNS = "UCRM\\Plugins\\Commands\\UPM";

// Create the Application.
$application = new Application();

// Set any relevant application information.
$application->setName("UCRM Plugin Manager");
$application->setVersion("1.0.0");

// Add our custom commands, dynamically...
$application->addCommands(loadCommands(UPM_COMMAND_PATH, UPM_COMMAND_FQNS));

// Finally, run the console application.
/** @noinspection PhpUnhandledExceptionInspection */
$application->run();
