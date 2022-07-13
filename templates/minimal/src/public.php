<?php
declare(strict_types=1);

use Ubnt\UcrmPluginSdk\Service\PluginLogManager;

chdir(__DIR__);

/** {{TEMPLATE_CMD:REMOVE_LINE}} @noinspection PhpIncludeInspection */
require_once __DIR__ . "/vendor/autoload.php";

// Get UCRM log manager.
$log = PluginLogManager::create();
$log->appendLog(
    sprintf(
        "Executed from public URL: %s",
        file_get_contents("php://input")
    )
);
