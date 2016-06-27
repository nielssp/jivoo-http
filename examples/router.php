<?php
use Jivoo\Http\ActionRequest;
use Jivoo\Http\Route\CallableRoute;
use Jivoo\Http\Route\CallableScheme;
use Jivoo\Http\Router;
use Jivoo\Http\SapiServer;
use Psr\Http\Message\ResponseInterface;

require '../vendor/autoload.php';

$router = new Router();
$router->addScheme(new CallableScheme());

$router->root(function (ActionRequest $request, ResponseInterface $response, $parameters) {
    $response->getBody()->write('Hello, World');
    return $response;
});


$router->error(function (ActionRequest $request, ResponseInterface $response, $parameters) {
    $response->getBody()->write('Page not found');
    return $response;
});

$router->match('foo', function (ActionRequest $request, ResponseInterface $response, $parameters) {
    $response->getBody()->write('Foo');
    return $response;
});

$router->match('foo/:bar', function (ActionRequest $request, ResponseInterface $response, $parameters) {
    $response->getBody()->write('Foo: ' . $parameters['bar']);
    return $response;
});

$server = new SapiServer($router);
$server->listen();