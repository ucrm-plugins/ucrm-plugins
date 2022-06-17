<?php
declare(strict_types=1);

use Com\Tecnick\Barcode\Barcode;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;

chdir(__DIR__);

require __DIR__ . '/vendor/autoload.php';

$logger = new Logger('UBarcode');
$logger->pushHandler(new StreamHandler('data/plugin.log', LogLevel::WARNING));

try {
    $barcode = new Barcode();
    $request = Request::createFromGlobals();
    $barcode->getBarcodeObj(
        (string) $request->get('type'),
        (string) $request->get('code'),
        (int) $request->get('width'),
        (int) $request->get('height'),
        (string) $request->get('color')
    )->getSvg();
} catch (Exception $exception) {
    $logger->error($exception->getMessage());
}
