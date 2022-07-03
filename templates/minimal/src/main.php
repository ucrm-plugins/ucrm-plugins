<?php
declare(strict_types=1);

chdir(__DIR__);

require_once __DIR__ . "/vendor/autoload.php";

// Get UCRM log manager.
$log = \Ubnt\UcrmPluginSdk\Service\PluginLogManager::create();
$log->appendLog("Finished execution.");
