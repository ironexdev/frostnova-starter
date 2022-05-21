<?php declare(strict_types=1);

use DI\ContainerBuilder;
use Frostnova\Core\Kernel;

if ($_ENV["ERROR_REPORTING"] === "true") {
    error_reporting(E_ALL);
    ini_set("display_errors", "On");
}

if ($_ENV["FORCE_HTTPS"] === "true") {
    if (!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] === "off") {
        echo "Website can only be accessed via HTTPS protocol";
        exit;
    }
}

const APP_DIRECTORY = __DIR__;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

date_default_timezone_set("UTC");

$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(true);
$containerBuilder->useAnnotations(false);
$containerBuilder->addDefinitions(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "config-di.php");
if ($_ENV["ENVIRONMENT"] === "production") {
    $containerBuilder->enableCompilation(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "var" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "php-di");
}

$container = $containerBuilder->build();

try {
    $container->make(Kernel::class); // Start the app using Frostnova framework
} catch (Throwable $throwable) {
    $errorCode = $throwable->getCode() ?? 500;

    if ($_ENV["ENVIRONMENT"] === "development") { // Development
        // Throw server error
        throw $throwable;
    } else {
        if ($errorCode >= 400 && $errorCode < 500) {
            http_response_code(404); // Throw 404 instead of client error
        } else {
            http_response_code($errorCode); // Throw any server error
        }
    }
}
