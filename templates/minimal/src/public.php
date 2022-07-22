<?php
/** {% REMOVE_LINE %} @noinspection PhpUndefinedNamespaceInspection, PhpUndefinedClassInspection */
declare(strict_types=1);

use Ubnt\UcrmPluginSdk\Service\PluginLogManager;

chdir(__DIR__);

/** {% REMOVE_LINE %} @noinspection PhpIncludeInspection */
require_once __DIR__ . "/vendor/autoload.php";

// Get UCRM log manager.
$log = PluginLogManager::create();
$log->appendLog(
    sprintf(
        "Executed from public URL: %s",
        file_get_contents("php://input")
    )
);
