<?php

use Frostnova\Core\MiddlewareStack\MiddlewareStack;
use Frostnova\Core\Router;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tuupola\Middleware\CorsMiddleware;

const DS = DIRECTORY_SEPARATOR;

return [
    /* Custom ****************************************************************/
    /*************************************************************************/
    // Implements PSR-15
    Router::class => DI\factory(function (ContainerInterface $container, ResponseFactoryInterface $responseFactory) {
        $routes = require_once(APP_DIRECTORY . DS . ".." . DS . "config" . DS . "api" . DS . "routes.php");
        return new Router($responseFactory, $routes);
    }),

    // Implements PSR-15
    RequestHandlerInterface::class => DI\factory(function (
        ResponseInterface $defaultResponse,
        CorsMiddleware    $corsMiddleware,
        Router            $router
    ): RequestHandlerInterface {
        return new MiddlewareStack(
            $defaultResponse->withStatus(404), // Default/fallback response
            $corsMiddleware, // Add CORS headers to the response if needed
            // Add other middlewares
            $router
        );
    }),

    /* Middlewares ***********************************************************/
    /*************************************************************************/
    CorsMiddleware::class => DI\factory(function () {
        return new CorsMiddleware([
            "origin" => [ // Access-Control-Allow-Origin
                "http://client.local"
            ],
            "methods" => [ // Access-Control-Allow-Methods
                "DELETE",
                "GET",
                "HEAD",
                "OPTIONS",
                "PATCH",
                "POST",
                "PUT"
            ],
            "headers.allow" => [ // Access-Control-Allow-Headers

            ],
            "headers.expose" => [ // Access-Control-Expose-Headers

            ],
            "credentials" => true, // Access-Control-Allow-Credentials
            "cache" => 0
        ]);
    }),

    // PSR interfaces ********************************************************/
    /*************************************************************************/

    // PSR-7
    ResponseInterface::class => DI\factory(function (ResponseFactoryInterface $responseFactory) {
        return $responseFactory->createResponse();
    }),
    ResponseFactoryInterface::class => DI\autowire(Psr17Factory::class),

    // PSR-15
    ServerRequestInterface::class => DI\factory(function (
        ServerRequestFactoryInterface $serverRequestFactory,
        StreamFactoryInterface        $streamFactory,
        UploadedFileFactoryInterface  $uploadedFileFactory,
        UriFactoryInterface           $uriFactory
    ) {
        $creator = new ServerRequestCreator(
            $serverRequestFactory,
            $uriFactory,
            $uploadedFileFactory,
            $streamFactory
        );

        $serverRequest = $creator->fromGlobals();

        $contentType = $serverRequest->getHeaderLine("Content-Type");

        // Parse request body, because Nyholm\Psr7Server doesn't parse JSON requests
        if ($contentType === "application/json") {
            if (!$serverRequest->getParsedBody()) {
                $content = $serverRequest->getBody()->getContents();
                $data = json_decode($content, true);

                if ($data === false || json_last_error() !== JSON_ERROR_NONE) {
                    throw new InvalidArgumentException(json_last_error_msg() . " in body: '" . $content . "'");
                }

                $serverRequest = $serverRequest->withParsedBody($data);
            }
        }

        return $serverRequest;
    }),

    // PSR-17
    ServerRequestFactoryInterface::class => DI\autowire(Psr17Factory::class),
    StreamFactoryInterface::class => DI\autowire(Psr17Factory::class),
    UploadedFileFactoryInterface::class => DI\autowire(Psr17Factory::class),
    UriFactoryInterface::class => DI\autowire(Psr17Factory::class)
];
